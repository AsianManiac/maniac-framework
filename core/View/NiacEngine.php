<?php

/**
 * Niac Template Engine for the Maniac Framework.
 *
 * Compiles Niac templates into PHP files, supporting Blade-like syntax with
 * additional features. Handles layouts, sections, components, slots, and more.
 *
 * @version 1.2.4
 * @license MIT
 * @package Core\View
 */

namespace Core\View;

use Exception;
use Throwable;

class NiacEngine implements ViewEngineInterface
{
    /**
     * Path to the views directory
     * @var string
     */
    protected string $viewsPath;

    /**
     * Path to the cache directory
     * @var string
     */
    protected string $cachePath;

    /**
     * Ordered list of compilers to process templates
     * @var array
     */
    protected array $compilers = [
        'Comments',    // Remove comments first
        'Extends',     // Handle layout extension
        'Metadata',    // Process @title, @meta
        'Components',  // Handle @component, @slot
        'Echos',       // {{ $var }}, {!! $var !!}
        'EscapedEchos', // @{{ $var }}
        'Php',         // @php, @endphp
        'Structures',  // @if, @foreach, @switch, etc.
        'Csrf',        // @csrf
        'Method',      // @method('PUT')
        'Include',     // @include('view')
        'Yield',       // @yield('section')
        'Section',     // @section('name')
        'Asset',       // @asset('path')
    ];

    /**
     * Current sections being processed
     * @var array
     */
    protected array $sections = [];

    /**
     * Section stack for nested sections
     * @var array
     */
    protected array $sectionStack = [];

    /**
     * Current layout being extended
     * @var string|null
     */
    protected ?string $layout = null;

    /**
     * Metadata for the view (title, meta tags)
     * @var array
     */
    protected array $metadata = [];

    /**
     * Slots for component rendering
     * @var array
     */
    protected array $slots = [];

    /**
     * Constructor
     *
     * @param string $viewsPath Path to views directory
     * @param string $cachePath Path to cache directory
     * @throws Exception If directories cannot be created or are not writable
     */
    public function __construct(string $viewsPath, string $cachePath)
    {
        $this->viewsPath = rtrim($viewsPath, '/\\');
        $this->cachePath = rtrim($cachePath, '/\\');

        if (!is_dir($this->cachePath) && !mkdir($this->cachePath, 0775, true)) {
            throw new Exception("Cannot create cache directory: {$this->cachePath}");
        }
        if (!is_writable($this->cachePath)) {
            throw new Exception("Cache directory is not writable: {$this->cachePath}");
        }
    }

    /**
     * Render a view template
     *
     * @param string $viewName View name (dot notation)
     * @param array $data Data to pass to the view
     * @return string Rendered content
     * @throws Exception If view cannot be found or rendered
     */
    public function render(string $viewName, array $data = []): string
    {
        $path = $this->resolveViewPath($viewName);
        if (!file_exists($path)) {
            throw new Exception("View not found: {$path}");
        }

        $compiledPath = $this->getCompiledPath($path);
        if (!file_exists($compiledPath) || $this->isExpired($path, $compiledPath)) {
            $this->compile($path, $compiledPath, $viewName === 'layouts.app');
        }

        // Reset state for new render
        $this->sections = [];
        $this->sectionStack = [];
        $this->metadata = [];
        $this->slots = [];

        return $this->evaluatePath($compiledPath, $data);
    }

    /**
     * Check if a view exists
     *
     * @param string $viewName View name (dot notation)
     * @return bool True if view exists
     */
    public function exists(string $viewName): bool
    {
        $path = $this->resolveViewPath($viewName);
        return file_exists($path);
    }

    /**
     * Resolve view path from dot notation or namespaced view
     *
     * @param string $viewName View name in dot notation or namespaced (e.g., mail::message)
     * @return string Full path to view file
     */
    protected function resolveViewPath(string $viewName): string
    {
        // Handle namespaced views (e.g., mail::message)
        if (strpos($viewName, '::') !== false) {
            [$namespace, $view] = explode('::', $viewName);
            if ($namespace === 'mail') {
                $mailPaths = config('mail.markdown.paths', []);
                foreach ($mailPaths as $path) {
                    $filePath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $view) . '.niac.php';
                    if (file_exists($filePath)) {
                        return $filePath;
                    }
                }
            }
            // Fallback to default views path if not found
            $viewName = $namespace . '/' . $view;
        }

        return $this->viewsPath . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $viewName) . '.niac.php';
    }

    /**
     * Get compiled path for a view
     *
     * @param string $viewPath Path to original view
     * @return string Path to compiled view
     */
    protected function getCompiledPath(string $viewPath): string
    {
        $relativePath = ltrim(str_replace($this->viewsPath, '', $viewPath), '/\\');
        return $this->cachePath . '/' . sha1($relativePath) . '.php';
    }

    /**
     * Check if compiled view is expired
     *
     * @param string $viewPath Original view path
     * @param string $compiledPath Compiled view path
     * @return bool True if needs recompilation
     */
    protected function isExpired(string $viewPath, string $compiledPath): bool
    {
        return config('app.debug', false) || !file_exists($compiledPath) || filemtime($viewPath) >= filemtime($compiledPath);
    }

    /**
     * Compile a view file
     *
     * @param string $viewPath Path to view file
     * @param string $compiledPath Path to save compiled file
     * @param bool $isLayout Whether the view is a layout file
     * @throws Exception If view cannot be read, written, or contains syntax errors
     */
    protected function compile(string $viewPath, string $compiledPath, bool $isLayout = false): void
    {
        $content = file_get_contents(str_replace('/', '\\', $viewPath));
        if ($content === false) {
            throw new Exception("Cannot read view: {$viewPath}");
        }

        error_log("Compiling view: $viewPath (isLayout: " . ($isLayout ? 'true' : 'false') . ")");
        $this->layout = null;
        foreach ($this->compilers as $compiler) {
            $method = "compile{$compiler}";
            if (method_exists($this, $method)) {
                error_log("Running compiler: $compiler");
                $content = $this->$method($content);
            }
        }

        // For layouts, wrap content in PHP output to preserve HTML
        if ($isLayout) {
            $content = "<?php ob_start(); ?>\n" . $content . "\n<?php echo ob_get_clean(); ?>";
        }

        // Handle layout extension for non-layout views
        if (!$isLayout && $this->layout) {
            $layoutPath = $this->resolveViewPath($this->layout);
            if (!file_exists($layoutPath)) {
                throw new Exception("Layout not found: {$layoutPath}");
            }
            $compiledLayoutPath = $this->getCompiledPath($layoutPath);
            if (!file_exists($compiledLayoutPath) || $this->isExpired($layoutPath, $compiledLayoutPath)) {
                $this->compile($layoutPath, $compiledLayoutPath, true);
            }
            $content = $this->addLayoutRendering($content, $compiledLayoutPath);
        }

        // Validate PHP syntax before saving
        $tempFile = tempnam(sys_get_temp_dir(), 'niac_');
        file_put_contents($tempFile, "<?php" . $content);
        exec("php -l $tempFile 2>&1", $output, $returnCode);
        if ($returnCode !== 0) {
            error_log("Syntax error in compiled view: " . implode("\n", $output));
            unlink($tempFile);
            throw new Exception("Syntax error in compiled view: {$compiledPath}\n" . implode("\n", $output));
        }
        unlink($tempFile);

        if (file_put_contents($compiledPath, $content) === false) {
            throw new Exception("Cannot write compiled view: {$compiledPath}");
        }
    }

    /**
     * Evaluate a compiled view file
     *
     * @param string $compiledPath Path to compiled file
     * @param array $data Data to pass to view
     * @return string Rendered content
     * @throws Exception If rendering fails
     */
    protected function evaluatePath(string $compiledPath, array $data): string
    {
        extract($data, EXTR_SKIP);
        $__engine = $this;
        $__data = $data;

        ob_start();
        try {
            include $compiledPath;
            error_log("Sections after rendering $compiledPath: " . print_r(array_keys($this->sections), true));
        } catch (Throwable $e) {
            ob_end_clean();
            $errorMessage = "Error rendering view: " . $e->getMessage() .
                "\nFile: " . $e->getFile() .
                "\nLine: " . $e->getLine();
            throw new Exception($errorMessage, 0, $e);
        }
        $output = ob_get_clean();
        return $output;
    }

    /**
     * Start a new section
     *
     * @param string $name Section name
     */
    public function startSection(string $name): void
    {
        $this->sectionStack[] = $name;
        ob_start();
        error_log("Started section: $name");
    }

    /**
     * End current section
     *
     * @throws Exception If no section is open
     */
    public function stopSection(): void
    {
        if (empty($this->sectionStack)) {
            throw new Exception("Mismatched @endsection");
        }
        $name = array_pop($this->sectionStack);
        $this->sections[$name] = ob_get_clean();
        error_log("Stopped section: $name");
    }

    /**
     * Render a section
     *
     * @param string $name Section name
     * @param string|null $default Default content if section not found
     * @return string Section content
     */
    public function renderSection(string $name, ?string $default = ''): string
    {
        if (!isset($this->sections[$name])) {
            error_log("Section '$name' not found. Available sections: " . print_r(array_keys($this->sections), true));
            return $default;
        }
        return $this->sections[$name];
    }

    /**
     * Start a new slot
     *
     * @param string $name Slot name
     */
    public function startSlot(string $name): void
    {
        $this->sectionStack[] = 'slot_' . $name;
        ob_start();
    }

    /**
     * End current slot
     *
     * @throws Exception If no slot is open
     */
    public function stopSlot(): void
    {
        if (empty($this->sectionStack)) {
            throw new Exception("Mismatched @endslot");
        }
        $name = array_pop($this->sectionStack);
        $this->slots[substr($name, 5)] = ob_get_clean();
    }

    /**
     * Render a slot
     *
     * @param string $name Slot name
     * @param string|null $default Default content if slot not found
     * @return string Slot content
     */
    public function renderSlot(string $name, ?string $default = ''): string
    {
        return $this->slots[$name] ?? $default;
    }

    /**
     * Get page title
     *
     * @param string|null $default Default title if not set
     * @return string|null
     */
    public function getTitle(?string $default = null): ?string
    {
        return $this->metadata['title'] ?? $default;
    }

    /**
     * Get meta tags
     *
     * @return array
     */
    public function getMetaTags(): array
    {
        return $this->metadata['meta'] ?? [];
    }

    /**
     * Add layout rendering code to compiled view
     *
     * @param string $childContent Child view content
     * @param string $compiledLayoutPath Path to compiled layout
     * @return string Combined content
     */
    protected function addLayoutRendering(string $childContent, string $compiledLayoutPath): string
    {
        $php = '<?php ';
        $php .= '$__env = $__engine; ';
        $php .= '$__data = array_merge($__data ?? [], get_defined_vars()); ';
        $php .= 'unset($__data["__engine"], $__data["__env"], $__data["__currentLoopData"], $__data["section"], $__data["key"], $__data["value"]); ';
        $php .= 'extract($__data, EXTR_SKIP); ';
        $php .= 'include(\'' . addslashes($compiledLayoutPath) . '\'); ';
        $php .= 'return; ';
        $php .= '?>';
        return $childContent . "\n" . $php;
    }

    // ======================
    // COMPILER METHODS
    // ======================

    /**
     * Compile comments
     *
     * @param string $content Template content
     * @return string Content without comments
     */
    protected function compileComments(string $content): string
    {
        return preg_replace('/\{---\s*(.*?)\s*---\}/s', '', $content);
    }

    /**
     * Compile extends directive
     *
     * @param string $content Template content
     * @return string Content with extends processed
     */
    protected function compileExtends(string $content): string
    {
        $pattern = '/^\s*@extends\s*\(\s*([\'"])(.+?)\1\s*\)\s*$/m';
        return preg_replace_callback($pattern, function ($matches) {
            $this->layout = $matches[2];
            return '';
        }, $content, 1);
    }

    /**
     * Compile metadata directives (@title, @meta)
     *
     * @param string $content Template content
     * @return string Content with metadata processed
     */
    protected function compileMetadata(string $content): string
    {
        $php = '<?php ';
        $content = preg_replace_callback('/@title\s*\(\s*(.+?)\s*\)/s', function ($matches) use (&$php) {
            $php .= '$__engine->metadata[\'title\'] = (' . trim($matches[1]) . '); ';
            return '';
        }, $content);

        $content = preg_replace_callback('/@meta\s*\(\s*([\'"])(.+?)\1\s*,\s*(.+?)\s*\)/s', function ($matches) use (&$php) {
            $php .= '$__engine->metadata[\'meta\'][' . var_export($matches[2], true) . '] = (' . trim($matches[3]) . '); ';
            return '';
        }, $content);

        return ($php === '<?php ' ? '' : $php . '?>') . $content;
    }

    /**
     * Compile component directives
     *
     * @param string $content Template content
     * @return string Content with components processed
     */
    protected function compileComponents(string $content): string
    {
        $pattern = '/@component\s*\(\s*([\'"])(.+?)\1(?:\s*,\s*(.+?))?\s*\)(.*?)@endcomponent/s';
        return preg_replace_callback($pattern, function ($matches) {
            $viewName = $matches[2];
            $data = isset($matches[3]) ? trim($matches[3]) : '[]';
            $content = $matches[4];

            // Process slots within the component
            $content = preg_replace_callback('/@slot\s*\(\s*([\'"])(.+?)\1\s*\)(.*?)@endslot/s', function ($slotMatches) {
                $slotName = $slotMatches[2];
                $slotContent = $slotMatches[3];
                return '<?php $__engine->startSlot(\'' . addslashes($slotName) . '\'); ?>' .
                    $slotContent .
                    '<?php $__engine->stopSlot(); ?>';
            }, $content);

            return '<?php echo $__engine->render(\'' . addslashes($viewName) . '\', array_merge($__data, ' . $data . ')); ?>';
        }, $content);
    }

    /**
     * Compile echo statements
     *
     * @param string $content Template content
     * @return string Content with echos processed
     */
    protected function compileEchos(string $content): string
    {
        $content = preg_replace('/\{\{\s*(.+?)\s*\}\}/s', '<?= htmlspecialchars((string) ($1), ENT_QUOTES, \'UTF-8\'); ?>', $content);
        $content = preg_replace('/\{\!!\s*(.+?)\s*!!\}/s', '<?= $1; ?>', $content);
        return $content;
    }

    /**
     * Compile escaped echo statements
     *
     * @param string $content Template content
     * @return string Content with escaped echos processed
     */
    protected function compileEscapedEchos(string $content): string
    {
        return preg_replace('/@\{\{(.+?)\}\}/s', '{{$1}}', $content);
    }

    /**
     * Compile PHP blocks
     *
     * @param string $content Template content
     * @return string Content with PHP blocks processed
     */
    protected function compilePhp(string $content): string
    {
        return preg_replace('/@php\s*(.*?)\s*@endphp/s', '<?php $1 ?>', $content);
    }

    /**
     * Compile control structures
     *
     * @param string $content Template content
     * @return string Content with structures processed
     */
    protected function compileStructures(string $content): string
    {
        // Standard control structures
        $patterns = [
            'if' => ['/@if\s*\((.*?)\)\s*/s', '<?php if ($1) { ?>'],
            'elseif' => ['/@elseif\s*\((.*?)\)\s*/s', '<?php } elseif ($1) { ?>'],
            'else' => ['/@else\s*/s', '<?php } else { ?>'],
            'endif' => ['/@endif\s*/s', '<?php } ?>'],
            'foreach' => ['/@foreach\s*\((.*?)\)\s*/s', '<?php foreach ($1) { ?>'],
            'endforeach' => ['/@endforeach\s*/s', '<?php } ?>'],
            'for' => ['/@for\s*\((.*?)\)\s*/s', '<?php for ($1) { ?>'],
            'endfor' => ['/@endfor\s*/s', '<?php } ?>'],
            'while' => ['/@while\s*\((.*?)\)\s*/s', '<?php while ($1) { ?>'],
            'endwhile' => ['/@endwhile\s*/s', '<?php } ?>'],
            'isset' => ['/@isset\s*\((.*?)\)\s*/s', '<?php if (isset($1)) { ?>'],
            'endisset' => ['/@endisset\s*/s', '<?php } ?>'],
            'emptyDirective' => ['/@empty\s*\((.*?)\)\s*/s', '<?php if (empty($1)) { ?>'],
            'endempty' => ['/@endempty\s*/s', '<?php } ?>'],
        ];

        // Compile standard structures
        foreach ($patterns as $name => [$pattern, $replacement]) {
            $content = preg_replace($pattern, $replacement, $content);
            error_log("Applied $name pattern to content");
        }

        // Handle forelse directive
        $content = preg_replace_callback(
            '/@forelse\s*\((.*?)\)\s*(.*?)\s*@empty\s*(.*?)\s*@endforelse/s',
            function ($matches) {
                $loop = trim($matches[1]);
                $loopContent = trim($matches[2]);
                $emptyContent = trim($matches[3]);
                $loopParts = explode(' as ', $loop);
                if (count($loopParts) !== 2) {
                    error_log("Invalid @forelse syntax: $loop");
                    return $matches[0];
                }
                $collection = trim($loopParts[0]);
                $variable = trim($loopParts[1]);
                return "<?php \$__currentLoopData = $collection; \$loop = new stdClass(); \$loop->index = 0; \$loop->count = is_countable(\$__currentLoopData) ? count(\$__currentLoopData) : 0; " .
                    "if (!empty(\$__currentLoopData)) { foreach (\$__currentLoopData as $variable) { \$loop->first = \$loop->index === 0; \$loop->last = \$loop->index === \$loop->count - 1; ?>\n" .
                    $loopContent . "\n<?php \$loop->index++; } } else { ?>\n" .
                    $emptyContent . "\n<?php } ?>";
            },
            $content
        );

        // Handle switch statements
        $content = preg_replace_callback(
            '/@switch\s*\((.*?)\)\s*(.*?)@endswitch/s',
            function ($matches) {
                $expression = trim($matches[1]);
                $body = $matches[2];

                // Process case statements
                $body = preg_replace_callback(
                    '/@case\s*\((.*?)\)\s*(.*?)(?=@case|@default|@endswitch|\z)/s',
                    function ($caseMatches) {
                        $caseValue = trim($caseMatches[1]);
                        $caseContent = trim($caseMatches[2]);
                        $caseContent = preg_replace('/@break\s*/', '', $caseContent);
                        return "    case $caseValue: ?>\n        " . $caseContent . "\n    <?php break;\n";
                    },
                    $body
                );

                // Process default case
                $body = preg_replace(
                    '/@default\s*(.*?)(?=@endswitch|\z)/s',
                    "    default: ?>\n        $1\n    <?php break;\n",
                    $body
                );

                return "<?php switch ($expression) {\n" . $body . "} ?>";
            },
            $content
        );

        return $content;
    }

    /**
     * Compile include directives
     *
     * @param string $content Template content
     * @return string Content with includes processed
     */
    protected function compileInclude(string $content): string
    {
        $pattern = '/@include\s*\(\s*([\'"])(.+?)\1(?:\s*,\s*(.+?))?\s*\)/s';
        return preg_replace_callback($pattern, function ($matches) {
            $viewName = $matches[2];
            $data = isset($matches[3]) ? trim($matches[3]) : '[]';
            return '<?php echo $__engine->render(\'' . addslashes($viewName) . '\', array_merge($__data, ' . $data . ')); ?>';
        }, $content);
    }

    /**
     * Compile CSRF directives
     *
     * @param string $content Template content
     * @return string Content with CSRF processed
     */
    protected function compileCsrf(string $content): string
    {
        return preg_replace('/@csrf\s*/', '<?= csrf_field(); ?>', $content);
    }

    /**
     * Compile method directives
     *
     * @param string $content Template content
     * @return string Content with method processed
     */
    protected function compileMethod(string $content): string
    {
        $pattern = '/@method\s*\(\s*([\'"])(PUT|POST|DELETE|PATCH)\1\s*\)/i';
        return preg_replace($pattern, '<input type="hidden" name="_method" value="$2">', $content);
    }

    /**
     * Compile yield directives
     *
     * @param string $content Template content
     * @return string Content with yields processed
     */
    protected function compileYield(string $content): string
    {
        $pattern = '/@yield\s*\(\s*([\'"])(.+?)\1(?:\s*,\s*(.+?))?\s*\)/s';
        return preg_replace_callback($pattern, function ($matches) {
            $name = $matches[2];
            $default = isset($matches[3]) ? $matches[3] : "''";
            return '<?= $__engine->renderSection(\'' . addslashes($name) . '\', ' . $default . '); ?>';
        }, $content);
    }

    /**
     * Compile section directives
     *
     * @param string $content Template content
     * @return string Content with sections processed
     */
    protected function compileSection(string $content): string
    {
        $content = preg_replace('/@section\s*\(\s*([\'"])(.+?)\1\s*\)/s', '<?php $__engine->startSection(\'$2\'); ?>', $content);
        $content = preg_replace('/@endsection\s*/s', '<?php $__engine->stopSection(); ?>', $content);
        return $content;
    }

    /**
     * Compile asset directives
     *
     * @param string $content Template content
     * @return string Content with assets processed
     */
    protected function compileAsset(string $content): string
    {
        $pattern = '/@asset\s*\(\s*([\'"])(.+?)\1\s*\)/s';
        return preg_replace($pattern, '<?= asset(\'$2\'); ?>', $content);
    }
}
