<?php

namespace Core\Database\Migrations;

use Exception;

/**
 * Creates new migration files based on a template.
 */
class MigrationCreator
{
    /**
     * The migration directory path.
     *
     * @var string
     */
    protected $path;

    /**
     * Create a new migration creator instance.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Create a new migration file.
     *
     * @param string $name The name of the migration.
     * @param string|null $create The table to create (if any).
     * @param string|null $table The table to modify (if any).
     * @return string The created file path.
     * @throws Exception
     */
    public function create(string $name, ?string $create = null, ?string $table = null): string
    {
        $stub = $this->getStub($create !== null);
        $timestamp = date('Y_m_d_His');
        $fileName = "{$timestamp}_{$name}.php";
        $filePath = $this->path . '/' . $fileName;

        // Derive table name from migration name if not provided
        $tableName = $create ?? $table ?? $this->guessTableName($name);

        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }

        $content = str_replace(
            ['{{ class }}', '{{ table }}', '{{ schema }}'],
            [
                $this->getClassName($name),
                $tableName,
                $this->getSchemaStub($create, $tableName)
            ],
            $stub
        );

        if (file_put_contents($filePath, $content) === false) {
            throw new Exception("Could not write to {$filePath}");
        }

        return $filePath;
    }

    /**
     * Get the appropriate migration stub.
     *
     * @param bool $create
     * @return string
     */
    protected function getStub(bool $create): string
    {
        $stub = $create
            ? <<<EOT
<?php

use Core\Database\Schema;
use Core\Database\Schema\Blueprint;
use Core\Database\Migrations\Migration;

class {{ class }} extends Migration
{
    public function up(): void
    {
        Schema::create('{{ table }}', function (Blueprint \$table) {
            {{ schema }}
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{{ table }}');
    }
}
EOT
            : <<<EOT
<?php

use Core\Database\Schema;
use Core\Database\Schema\Blueprint;
use Core\Database\Migrations\Migration;

class {{ class }} extends Migration
{
    public function up(): void
    {
        Schema::table('{{ table }}', function (Blueprint \$table) {
            {{ schema }}
        });
    }

    public function down(): void
    {
        Schema::table('{{ table }}', function (Blueprint \$table) {
            // Reverse changes here
        });
    }
}
EOT;

        return $stub;
    }

    /**
     * Get the schema stub for the migration.
     *
     * @param string|null $create
     * @param string $table
     * @return string
     */
    protected function getSchemaStub(?string $create, string $table): string
    {
        if ($create) {
            return <<<EOT
\$table->id();
            \$table->timestamps();
EOT;
        }
        return '// Define schema changes here';
    }

    /**
     * Convert the migration name to a class name.
     *
     * @param string $name
     * @return string
     */
    protected function getClassName(string $name): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    }

    /**
     * Guess the table name from the migration name.
     *
     * @param string $name
     * @return string
     */
    protected function guessTableName(string $name): string
    {
        $name = strtolower($name);
        if (str_starts_with($name, 'create_') && str_ends_with($name, '_table')) {
            return substr($name, 7, -6);
        }
        return str_replace(['create_', '_table'], ['', ''], $name);
    }
}
