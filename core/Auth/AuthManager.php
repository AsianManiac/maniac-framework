<?php

namespace Core\Auth;

use App\Models\User;

class AuthManager
{

    protected ?User $user = null; // Holds the authenticated user for THIS request

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function user(): ?User
    {
        return $this->user;
    }

    public function check(): bool
    {
        return $this->user !== null;
    }

    public function id(): ?int
    {
        // Assuming User model has an 'id' property
        return $this->user?->id;
    }

    // Add guest() method etc.
    public function guest(): bool
    {
        return !$this->check();
    }
}
