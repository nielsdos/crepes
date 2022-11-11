<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy extends BasePolicy
{
    private function basePerms(User $user): bool
    {
        return $user->perms >= User::PERMS_COURSE_MANAGER;
    }

    public function create(User $user): bool
    {
        return $this->basePerms($user);
    }

    public function update(User $user, Course $course): bool
    {
        return $this->basePerms($user) && $course->owner_id === $user->id;
    }

    public function delete(User $user, Course $course): bool
    {
        return $this->update($user, $course);
    }
}
