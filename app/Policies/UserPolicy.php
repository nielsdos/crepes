<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy extends BasePolicy
{
    public function index(User $user): bool
    {
        return false;
    }

    public function updateAdmin(User $user, string $id): bool
    {
        return false;
    }

    public function update(User $user, string $id): bool
    {
        return $id === 'me';
    }

    public function delete(User $user): bool
    {
        return false;
    }
}
