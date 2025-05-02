<?php

namespace Core\Database\Migrations;

/**
 * Base class for database migrations.
 *
 * Each migration should extend this class and implement `up` and `down` methods.
 */
abstract class Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    abstract public function up(): void;

    /**
     * Reverse the migration.
     *
     * @return void
     */
    abstract public function down(): void;
}
