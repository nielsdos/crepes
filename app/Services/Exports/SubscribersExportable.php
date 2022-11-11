<?php

namespace App\Services\Exports;

use App\Models\Course;
use App\Models\Subscription;

class SubscribersExportable implements Exportable
{
    public function __construct(private readonly Course $course, private readonly bool $requestsFullData)
    {
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return Subscription::join('session_groups', 'session_group_id', '=', 'session_groups.id')
                            ->join('users', 'user_id', '=', 'users.id')
                            ->where('session_groups.course_id', $this->course->id)
                            ->orderBy('session_group_id')
                            ->orderBy('lastname')
                            ->orderBy('firstname')
                            ->get();
    }

    public function map(mixed $data): array
    {
        $user = $data->user;

        $arr = [$data->groupIndex(), $user->lastname, $user->firstname];
        if ($this->requestsFullData) {
            $arr[] = $user->email;
        }
        $arr[] = $user->function;
        $arr[] = $user->member_nr;
        $arr[] = new \DateTime($data->created_at);

        return $arr;
    }

    public function heading(): array
    {
        $arr = [ucfirst(__('common.session_group')), __('common.lastname'), __('common.firstname')];
        if ($this->requestsFullData) {
            $arr[] = __('common.email');
        }
        $arr[] = __('common.function');
        $arr[] = __('common.member_nr');
        $arr[] = __('common.subscribed_on');

        return $arr;
    }
}
