<?php

use Core\Database\Schema;
use Core\Database\Schema\Blueprint;
use Core\Database\Migrations\Migration;

class CreatePermissionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['read', 'write', 'admin'])->default('read');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
}
