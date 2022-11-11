<?php

namespace App\Observers;

use App\Models\Course;
use App\Services\CourseDependentCache;

class CourseObserver
{
    public function saved(Course $course): void
    {
        CourseDependentCache::flush();
    }

    public function deleted(Course $course): void
    {
        CourseDependentCache::flush();
    }

    public function deleting(Course $course): void
    {
        if (! $course->isForceDeleting()) {
            // TODO: We already have this code to delete the subscriptions in case the course is soft deleted.
            //       However, it is not possible to soft delete courses (yet), so we can't test this.
            $course->subscriptions()->delete();
        }
    }
}
