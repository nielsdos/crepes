<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function deleting(User $user): void
    {
        if ($user->isForceDeleting()) {
            $user->subscriptions()->forceDelete();
            $user->courses()->forceDelete();
        } else {
            $user->subscriptions()->delete();
            $user->courses()->get()->each->delete();
        }
    }
}
