<div class="col">
    <div class="card shadow-sm h-100">
        <div class="card-body">
            <h4><a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{ $course->title }}" class="text-truncate overflow_tooltip d-block text-decoration-none" href="{{ route('course.show', ['course' => $course, 'slug' => $course->slug]) }}">{{ $course->title }}</a></h4>
            <div class="mb-3 fw-bold text-dates">
                @if($course->sessionGroups->count() === 1 && $course->sessions->count() <= 3)
                    @php($sessions = $course->sessions /* because there is only 1 session group, all these sessions belong to that one group */)
                    @if($sessions->count() === 1)
                        @svg('regular/calendar', 'fa-1x text-danger') @dateTime($sessions[0]->start) - @time($sessions[0]->end)
                    @else
                        <ul class="list-unstyled mb-0">
                            @foreach($sessions as $session)
                                <li>@svg('regular/calendar', 'fa-1x text-danger') @dateTime($session->start) - @time($session->end)</li>
                            @endforeach
                        </ul>
                    @endif
                @else
                    {{ __('common.complex_session_situation') }}
                @endif
            </div>
            @include('partials.course-card', ['course' => $course, 'extra' => false, 'subscription' => Auth::check() && $course->subscriptions->isNotEmpty() /* already filtered on user id */])
        </div>
    </div>
</div>
