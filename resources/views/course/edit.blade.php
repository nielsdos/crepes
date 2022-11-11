@extends('layouts.app')
@section('title'){{ __('acts.edit_course') }}: {{ $course->title }}@endsection
@section('titleicon')
@svg('solid/pencil')
@endsection
@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('course.index') }}">{{ __('acts.courses') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('course.index') }}/?y={{ $year }}">{{ $yearDisplay }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('course.show', [$course, $course->slug]) }}">{{ __('common.course') }}: {{ $course->title }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ __('acts.edit_course') }}</li>
    </ol>
</nav>
@include('partials.messages')

<script type="text/template" id="msg_time">{{ __('common.time_end_before_start') }}</script>
<script type="text/template" id="msg_last_date_violation">{{ __('common.last_date_violation') }}</script>
<script type="text/template" id="msg_date_before_previous_date">{{ __('common.date_before_previous_date') }}</script>
<script type="text/template" id="msg_date_after_next_date">{{ __('common.date_after_next_date') }}</script>
<script type="text/template" id="msg_overlapping_sessions">{{ __('common.overlapping_sessions') }}</script>

<form action="{{ route('course.update', $course) }}" method="post" id="form" class="was-validated">
@csrf
@method('PUT')
<div id="accordion" class="accordion" role="tablist" aria-multiselectable="true">
    <div class="accordion-item">
        <h2 class="accordion-header" role="tab" id="headingInfo">
            <button type="button" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#collapseInfo" aria-expanded="true" aria-controls="collapseInfo">
                @svg('solid/circle-info', 'fa-1x') {{ __('common.course_info') }}
            </button>
        </h2>
        <div id="collapseInfo" class="accordion-body collapse show" role="tabpanel" aria-labelledby="headingInfo">
            <div class="mb-3 row">
                <label for="course_name" class="col-sm-4 col-form-label text-md-end">{{ __('common.course_name') }}</label>

                <div class="col-md-6">
                    <input id="course_name" value="{{ $course->title }}" oninvalid="this.setCustomValidity('{{ __('validation.max_length_error', ['length' => 100]) }}')" oninput="this.setCustomValidity('')" pattern=".{0,100}" class="form-control" name="course_name" required>
                </div>
            </div>

            <div class="mb-3 row">
                <label for="last_date" class="col-sm-4 col-form-label text-md-end">{{ __('common.last_subscribe_date') }}</label>

                <div class="col-md-6">
                    <input id="last_date" value="{{ $course->last_date->toDateString() }}" type="date" class="form-control" name="last_date" required>
                </div>
            </div>

            <div class="mb-3 row">
                <label for="times" class="col-sm-4 col-form-label text-md-end">{{ __('common.course_times_given') }}</label>

                <div class="col-md-6">
                    <input id="times" type="number" value="{{ $sessionGroupCount }}" min="1" max="10" class="form-control" disabled>
                    <input type="hidden" value="{{ $sessionGroupCount }}" name="times">
                </div>
            </div>

            <div class="mb-3 row">
                <label for="session_count" class="col-sm-4 col-form-label text-md-end">{{ __('common.course_session_count') }}</label>

                <div class="col-md-6">
                    <input id="session_count" type="number" value="{{ $sessionCount }}" min="1" max="10" class="form-control" disabled>
                    <input type="hidden" value="{{ $sessionCount }}" name="session_count">
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-md-4 col-form-label text-md-end" for="notify_me">{{ __('common.course_send_email') }}</label>
                <div class="col-md-6 mt-auto mb-auto">
                    <input class="form-check-input" aria-label="{{ __('common.course_send_email') }}" type="checkbox" name="notify_me" id="notify_me" checked>
                </div>
            </div>

            <div class="mb-3 row">
                <label for="description" class="col-sm-4 col-form-label text-md-end">{{ __('common.course_description') }}</label>

                <div class="col-md-6">
                    <textarea id="description" name="description" rows="7" class="form-control" required>{{ $course->description }}</textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="accordion-item">
        <h2 class="accordion-header" role="tab" id="headingDescriptions">
            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#collapseDescriptions" aria-expanded="false" aria-controls="collapseDescriptions">
                @svg('solid/list', 'fa-1x') {{ __('common.descriptions') }}
            </button>
        </h2>
        <div id="collapseDescriptions" class="collapse" role="tabpanel" aria-labelledby="headingDescriptions">
            <div class="accordion-body">
            @foreach($course->sessionGroups[0]->sessions as $i => $session)
                <div class="mb-3 row">
                    <label for="desc[{{ $i }}]" class="col-sm-4 col-form-label text-md-end">{{ __('common.session_description_for') }}{{ $i + 1 }}</label>

                    <div class="col-md-6">
                        <textarea id="desc[{{ $i }}]" name="desc[{{ $i }}]" rows="4" class="form-control" required>{{ $session->sessionDescription->description }}</textarea>
                    </div>
                </div>
            @endforeach
            </div>
        </div>
    </div>
    <div class="accordion-item">
        <h2 class="accordion-header" role="tab" id="headingGroups">
            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#collapseGroups" aria-expanded="false" aria-controls="collapseGroups">
                @svg('regular/calendar', 'fa-1x') {{ __('common.session_groups') }}
            </button>
        </h2>
        <div id="collapseGroups" class="collapse" role="tabpanel" aria-labelledby="headingGroups">
            <div class="accordion-body">
            @foreach($course->sessionGroups as $i => $sg)
                <h4 class="col-sm-4 text-md-end smalltitle">Sessiegroep {{ $i + 1 }}</h4>
                <hr>
                <div class="row mb-4-5">
                    <label for="group_max_ppl[{{ $i }}]" class="col-sm-4 col-form-label text-md-end">{{ __('common.group_max_ppl_for') }}</label>

                    <div class="col-md-6">
                        <input id="group_max_ppl[{{ $i }}]" type="number" value="{{ $sg->max_ppl }}" min="2" max="65535" class="form-control" name="group_max_ppl[{{ $i }}]" required>
                    </div>
                </div>

                @foreach($sg->sessions as $j => $session)
                    <div class="mb-3 row">
                        <label for="session_location[{{ $i }}][{{ $j }}]" class="col-sm-4 col-form-label text-md-end">{{ __('common.session_location_for') }}{{ $j + 1 }}</label>

                        <div class="col-md-6">
                            <input id="session_location[{{ $i }}][{{ $j }}]" value="{{ $session->location }}" class="form-control" oninvalid="this.setCustomValidity('{{ __('validation.max_length_error', ['length' => 150]) }}')" oninput="this.setCustomValidity('')" pattern=".{0,150}" name="session_location[{{ $i }}][{{ $j }}]" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="session_date[{{ $i }}][{{ $j }}]" class="col-sm-4 col-form-label text-md-end">{{ __('common.session_date_for') }}{{ $j + 1 }}</label>

                        <div class="col-md-6">
                            <input id="session_date[{{ $i }}][{{ $j }}]" value="{{ $session->start->toDateString() }}" type="date" class="form-control" name="session_date[{{ $i }}][{{ $j }}]" required>
                        </div>
                    </div>
                    <div class="row mb-4-5">
                        <label for="session_starttime[{{ $i }}][{{ $j }}]" class="col-sm-4 col-form-label text-md-end">{{ __('common.session_time_for') }}{{ $j + 1 }}</label>

                        <div class="col-md-3">
                            <input type="time" value="@time($session->start->toTimeString())" id="session_starttime[{{ $i }}][{{ $j }}]" class="form-control" name="session_starttime[{{ $i }}][{{ $j }}]" required>
                        </div>
                        <div class="col-md-3">
                            <input type="time" value="@time($session->end)" id="session_endtime[{{ $i }}][{{ $j }}]" class="form-control" name="session_endtime[{{ $i }}][{{ $j }}]" required>
                        </div>
                    </div>
                @endforeach
            @endforeach
            </div>
        </div>
    </div>
</div>
<div class="mt-3">
    <div class="row">
        <div class="col-md-6 offset-md-4 btn-group">
            <button id="save_and_back" type="submit" name="save" value="close" class="btn btn-primary">
                {{ __('acts.save_and_back') }}
            </button>
            <button id="save" type="submit" name="save" class="btn btn-outline-primary">
                {{ __('acts.save') }}
            </button>
        </div>
    </div>
</div>
</form>
<script src="{{ url(mix('js/editcourse.js')) }}"></script>
@endsection
