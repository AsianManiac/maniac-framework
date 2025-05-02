<?php

namespace App\Models;

use Core\Mvc\Model;

class Post extends Model
{
    protected array $fillable = [];
    protected string $table = 'posts';
}