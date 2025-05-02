<?php

namespace App\Models;

use Core\Mvc\Model;

class User extends Model
{
    protected array $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'email_verified_at',
    ];

    protected ?string $table = 'users';

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions', 'user_id', 'permission_id');
    }
}
