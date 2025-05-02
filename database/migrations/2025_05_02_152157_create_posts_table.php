<?php

use Core\Database\Schema;
use Core\Database\Schema\Blueprint;
use Core\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->engine('InnoDB'); // Use InnoDB (FULLTEXT supported in MySQL 5.6+)
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->string('title');
            $table->text('content');
            $table->boolean('is_published')->default(false);
            $table->datetime('published_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id', 'users')->onDelete('CASCADE');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
}
