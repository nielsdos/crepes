@extends('layouts.app')
@section('title'){{ __('common.course') }}: {{ $course->title }}@endsection
@section('titleicon')
@svg('solid/book-open')
@endsection
@section('buttons')
    <div class="btn-group">
    @can('update', $course)
        <a href="{{ route('course.edit', $course) }}" class="btn btn-ico btn-primary">
            @svg('solid/pencil', 'ico-l--1')
        </a>
    @endcan
    @can('delete', $course)
        <a href="#removeModal" class="btn btn-ico btn-danger" data-bs-toggle="modal" data-bs-target="#removeModal">
            @svg('solid/trash-can', 'ico-l--1')
        </a>
    @endcan
    </div>
@endsection
@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('course.index') }}">{{ __('acts.courses') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('course.index') }}/?y={{ $year }}">{{ $yearDisplay }}</a></li>
        <li class="breadcrumb-item active d-none d-sm-inline" aria-current="page">{{ __('common.course') }}: {{ $course->title }}</li>
    </ol>
</nav>
@include('partials.messages')
@php
$tooLate = $course->tooLateToSubscribe();
$myGroup = $subscription ? $subscription->groupIndex() : -1;
@endphp

@can('delete', $course)
<div class="modal fade" id="removeModal" tabindex="-1" role="dialog" aria-labelledby="removeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeModalLabel">{{ __('acts.delete_course') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    {{ __('acts.delete_course_confirmation') }}
                    <br>
                    {{ __('acts.action_not_undoable') }}
                </p>
            </div>
            <div class="modal-footer">
                <form action="{{ route('course.destroy', $course) }}" method="post">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">{{ __('common.yes') }}</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.no') }}</button>
            </div>
        </div>
    </div>
</div>
@endif

@if($subscription)
<div class="bd-callout shadow-sm bd-callout-success mb-3">
    <h4>{{ __('common.has_subscribed') }}</h4>
    <p>
        {!! \Illuminate\Support\Str::inlineMarkdown(__('common.you_have_registered', ['time' => $subscription->created_at->diff(\Carbon\Carbon::now())->h < 1 ? __('common.just_now') : $subscription->created_at->diffForHumans()]), ['html_input' => 'escape', 'allow_unsafe_links' => false]) !!}
        <br>
        {{ __('common.subscribed_in') }} <a href="#group{{ $myGroup }}">{{ __('common.session_group') }} #{{ $myGroup }}</a>.
    </p>
</div>
@endif

<div class="card shadow-sm mb-4-5">
    <div class="card-body">
        @include('partials.course-card', ['course' => $course, 'extra' => true, 'subscription' => $subscription])
    </div>
</div>
@include('partials.overflow-tooltip')

<div class="row">
    <div class="col">
        <h4 class="smalltitle">@svg('solid/calendar', 'fa-xs') {{ __('common.session_groups_and_subscribe') }}</h4>
    </div>
</div>

@if($subscription)
<div class="modal fade" id="unsModal" tabindex="-1" role="dialog" aria-labelledby="unsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="unsModalLabel">{{ __('acts.unsubscribe') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('acts.unsubscribe_confirmation') }}</p>
            </div>
            <div class="modal-footer">
                <form action="{{ route('course.unsubscribe', $subscription) }}" method="post">
                    @csrf
                    <button class="btn btn-danger">{{ __('common.yes') }}</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.no') }}</button>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card shadow-sm mb-4-5">
    <div class="card-body">
        <div class="row vdivide mb--4-5">
            @foreach($course->sessionGroups as $i => $sg)
                @if($course->sessionGroups->count() === 1)
                <div class="col mb-4-5" id="group{{ $i + 1 }}">
                @else
                <div class="col-lg-12 col-xl-6 mb-4-5" id="group{{ $i + 1 }}">
                @endif
                    <h4 class="card-title{{ $i + 1 === $myGroup ? ' text-success' : '' }}">{{ ucfirst(__('common.session_group' )) }} #{{ $i + 1 }}</h4>
                    <h4 class="card-subtitle text-muted small mb-3">{{ __('common.max_ppl', ['max' => $sg->max_ppl]) }}</h4>

                    @php
                    $uniqueLocation = $sg->sessions->uniqueStrict('location');
                    $shouldShowLocationsIndividually = ! ($uniqueLocation->count() === 1 && $sg->sessions->count() > 1);
                    $uniqueLocation = $uniqueLocation->count() === 1 ? $uniqueLocation->first()->location : null;
                    @endphp

                    @unless($shouldShowLocationsIndividually)
                    <div class="px-2 pt-3 pb-1 row">
                        <div class="col-lg-{{$course->sessionGroups->count() === 1 ? 4 : 6}} col-md-6">
                            <div class="ps-1">
                                <h6 class="fw-bold">@svg('solid/location-dot', 'text-danger'){{ __('common.where') }}</h6>
                                <p class="fw-light lh-sm">
                                    {{ __('common.all_sessions_take_place_at') }}
                                    <br>
                                    @if(\App\Http\Controllers\CourseController::couldBeAValidStreetAddress($uniqueLocation))
                                    <a href="https://maps.google.com/?q={{ urlencode($uniqueLocation) }}" target="_blank" rel="noopener noreferrer">{{ $uniqueLocation }}</a>
                                    @else
                                    {{ $uniqueLocation }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if($showMapOnCourseDetails && $uniqueLocation && \App\Http\Controllers\CourseController::couldBeAValidStreetAddress($uniqueLocation))
                        @include('partials.google-maps-embed', ['location' => $uniqueLocation])
                        @endif
                    </div>
                    @endif

                    @if($course->sessionGroups->count() === 1 /* adapt layout due to column constraints in the parent */)
                    <div class="row grid row-cols-1 row-cols-md-2 row-cols-lg-3">
                    @else
                    <div class="row grid row-cols-1 row-cols-md-2">
                    @endif
                        @foreach($sg->sessions as $j => $session)
                        <div class="px-2 py-2">
                            <div class="rounded session-square px-3 pt-3 pb-1">
                                <h5 class="fw-light mb-3">{{ __('common.session') }} #{{ $j + 1 }}</h5>
                                <div class="row">
                                    @if($shouldShowLocationsIndividually)
                                    <div class="col">
                                        <h6 class="fw-bold">@svg('solid/location-dot', 'text-danger'){{ __('common.where') }}</h6>
                                        <p class="fw-light lh-sm">
                                            @if(\App\Http\Controllers\CourseController::couldBeAValidStreetAddress($session->location))
                                            <a href="https://maps.google.com/?q={{ urlencode($session->location) }}" target="_blank" rel="noopener noreferrer">{{ $session->location }}</a>
                                            @else
                                            {{ $session->location }}
                                            @endif
                                        </p>
                                    </div>
                                    @endif
                                    <div class="col">
                                        <h6 class="fw-bold">@svg('regular/calendar', 'text-danger'){{ __('common.when') }}</h6>
                                        <p class="fw-light lh-sm">
                                            @date($session->start)
                                            <br>
                                            @time($session->start) - @time($session->end)
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @if($showMapOnCourseDetails && $uniqueLocation && $sg->sessions->count() === 1 && \App\Http\Controllers\CourseController::couldBeAValidStreetAddress($uniqueLocation))
                        <div class="mt-2 sole-session-map-container">
                        @include('partials.google-maps-embed', ['location' => $uniqueLocation])
                        </div>
                        @endif
                    </div>
                    <div class="mb-3"></div> {{-- spacer --}}

                    @if($course->owner_id !== Auth::id())
                        @if($tooLate && !$subscription)
                            {{ __('acts.subscribe_too_late') }}
                        @elseif($sg->isFull() && !$subscription)
                            {{ __('common.group_full') }}
                        @else
                            @auth
                                @if($subscription)
                                    @if($myGroup === $i + 1)
                                        {{ __('common.already_subscribed_this') }}
                                        <br>
                                        @if($tooLate)
                                            {{ __('acts.unsubscribe_too_late') }}
                                        @else
                                            <a href="#unsModal" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#unsModal">{{ __('acts.unsubscribe') }}</a>
                                        @endif
                                    @else
                                        {{ __('common.already_subscribed') }}
                                    @endif
                                @else
                                    <form action="{{ route('course.subscribe', $sg) }}" method="post">
                                        @csrf
                                        <button class="btn btn-primary">{{ __('acts.subscribe') }}</button>
                                    </form>
                                @endif
                            @else
                                {{ __('acts.login_to_subscribe') }}
                            @endauth
                        @endif
                    @else
                        {{ __('acts.course_manager_cannot_subscribe') }}
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <h4 class="smalltitle">@svg('solid/list', 'fa-xs') {{ __('common.sessions') }}</h4>
    </div>
</div>

<div class="card shadow-sm mb-4-5">
    <div class="card-body">
        <div class="row vdivide mb--4-5">
            @php($sessions = $course->sessionGroups[0]->sessions)
            @foreach($sessions as $i => $session)
                @if($sessions->count() === 1)
                <div class="col mb-4-5">
                @else
                <div class="col-md-12 col-lg-6 mb-4-5">
                @endif
                    <h4 class="card-title">{{ __('common.session') }} #{{ $i + 1 }}</h4>
                    @description($session->sessionDescription->description)
                </div>
            @endforeach
        </div>
    </div>
</div>

@can('update', $course)
<div class="row">
    <div class="col">
        <div class="d-flex">
            <div class="w-100">
                <h4 class="smalltitle">@svg('solid/pen-to-square', 'fa-xs') {{ __('acts.subscriptions') }}</h4>
            </div>
            @if($course->subscriptions()->count() > 0)
            <div class="ms-auto titlebuttons">
                <div class="btn-group">
                    <a class="btn btn-light" href="{{ route('course.export.csv', $course) }}" target="_blank">@svg('solid/file-lines', 'fa-1x') {{ __('acts.export_csv') }}</a>
                    <a class="btn btn-success" href="{{ route('course.export.xlsx', $course) }}" target="_blank">@svg('solid/file-excel', 'fa-1x') {{ __('acts.export_excel') }}</a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4-5">
    <div class="card-body">
        <div class="row mb--4-5">
            @foreach($course->sessionGroups as $i => $sg)
                <div class="col-12 mb-4-5">
                    <h4 class="card-title">{{ ucfirst(__('common.session_group' )) }} #{{ $i + 1 }}</h4>
                    <h4 class="card-subtitle text-muted small mb-3">{{ __('common.ppl_count', ['current' => $sg->subscriptions->count(), 'max' => $sg->max_ppl]) }}</h4>

                    @if($sg->subscriptions->count() === 0)
                        {{ __('common.no_subscriptions_yet') }}
                    @else
                        <div class="table-responsive table-no-level">
                            <table class="table table-hover table-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">{{ __('common.lastname') }}</th>
                                        <th scope="col">{{ __('common.firstname') }}</th>
                                        @if(Auth::user()->isAdmin())
                                        <th scope="col">{{ __('common.email') }}</th>
                                        @endif
                                        <th scope="col">{{ __('common.function') }}</th>
                                        <th scope="col">{{ __('common.member_nr') }}</th>
                                        <th scope="col">{{ __('common.subscribed_on') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sg->subscriptions->sortBy('user.lastname')->sortBy('user.firstname') as $subscription)
                                        <tr>
                                            <td>{{ $subscription->user->lastname }}</td>
                                            <td>{{ $subscription->user->firstname }}</td>
                                            @if(Auth::user()->isAdmin())
                                            <td>{{ $subscription->user->email }}</td>
                                            @endif
                                            <td>
                                            @if($subscription->user->function)
                                                {{ $subscription->user->function }}
                                            @else
                                                <em>{{ __('common.not_provided') }}</em>
                                            @endif
                                            </td>
                                            <td>
                                            @if($subscription->user->member_nr)
                                                {{ $subscription->user->member_nr }}
                                            @else
                                                <em>{{ __('common.not_provided') }}</em>
                                            @endif
                                            </td>
                                            <td>@date($subscription->created_at)</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endcan

@endsection
