<?php

namespace App\Policies;

use App\Models\User;

class SettingPolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        return false;
    }
}
