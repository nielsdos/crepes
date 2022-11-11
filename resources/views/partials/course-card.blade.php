@if(! ($extra && $subscription))
    <h6 class="card-subtitle mb-2 text-muted small">
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
    </h6>
@endif
<h6 class="card-subtitle mb-2 text-muted small">
    @svg('solid/rotate-right', 'fa-1x')
    {{ __('common.course_group_times_desc', ['times' => $course->sessionGroups->count()]) }}
</h6>
<h6 class="card-subtitle mb-2 text-muted small">
    @svg('solid/list', 'fa-1x')
    @php($session_count = $course->sessions->count() / $course->sessionGroups->count())
    {{ trans_choice('common.course_sessions_desc', $session_count, ['times' => $session_count]) }}
</h6>
<h6 class="card-subtitle mb-2 text-muted small ellipsis">
    @svg('solid/user-tie', 'fa-1x')
    {{ $course->owner->fullname() }}
</h6>
@if($extra)
<h6 class="card-subtitle mb-2 text-muted small">
    @svg('solid/plus', 'fa-1x')
    @dateTime($course->created_at)
</h6>
@if($course->updated_at != $course->created_at)
<h6 class="card-subtitle mb-2 text-muted small">
    @svg('solid/pencil', 'fa-1x')
    @dateTime($course->updated_at)
</h6>
@endif
@endif
<div class="mt-4 mb-2">
@description($course->description)
</div>
