@if(! ($extra && $subscription))
    <span class="card-subtitle mb-1 text-muted small">
        @if($subscription)
            @svg('solid/check', 'text-success fa-1x')
            {{ __('common.subscribed') }}
        @else
            @if($course->tooLateToSubscribe())
                @svg('solid/clock', 'text-danger fa-1x')
            @else
                @svg('solid/clock', 'text-success fa-1x')
            @endif
            {{ __('common.last_subscribe_date_short') }} @date($course->last_date)
        @endif
    </span>
@endif
@php($sessionGroupsCount = $course->sessionGroups->count())
<span class="card-subtitle mb-1 text-muted small">
    @svg('solid/rotate-right', 'fa-1x')
    {{ trans_choice('common.course_group_times_desc', $sessionGroupsCount, ['times' => $sessionGroupsCount]) }}
</span>
<span class="card-subtitle mb-1 text-muted small">
    @svg('solid/list', 'fa-1x')
    @php($session_count = $course->sessions->count() / $sessionGroupsCount)
    {{ trans_choice('common.course_sessions_desc', $session_count, ['times' => $session_count]) }}
</span>
<span class="card-subtitle mb-1 text-muted small ellipsis">
    @svg('solid/user-tie', 'fa-1x')
    {{ $course->owner->fullname() }}
</span>
@if($extra)
<span class="card-subtitle mb-1 text-muted small">
    @svg('solid/plus', 'fa-1x')
    @dateTime($course->created_at)
</span>
@if($course->updated_at != $course->created_at)
<span class="card-subtitle mb-1 text-muted small">
    @svg('solid/pencil', 'fa-1x')
    @dateTime($course->updated_at)
</span>
@endif
@endif
<div class="mt-4 mb-2">
@description($course->description)
</div>
