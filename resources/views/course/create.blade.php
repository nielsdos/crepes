@extends('layouts.app')
@section('title', __('acts.create_course'))
@section('titleicon')
@svg('solid/plus')
@endsection
@section('content')
@include('partials.messages')

<ul id="progressbar">
    <li class="active">@svg('solid/circle-question'){{ __('common.course_info') }}</li>
    <li>@svg('solid/list'){{ __('common.descriptions') }}</li>
    <li>@svg('regular/calendar'){{ __('common.session_groups')}}</li>
</ul>

<script type="text/template" id="description_template">
    <div class="mb-3 row">
        <label for="{name}" class="col-sm-4 col-form-label text-md-end">{{ __('common.session_description_for') }}{num}</label>

        <div class="col-md-6">
            <textarea id="{name}" name="{name}" rows="4" class="form-control" required></textarea>
        </div>
    </div>
</script>

<script type="text/template" id="group_header_template">
    <h4 class="col-sm-4 text-md-end smalltitle">{{ ucfirst(__('common.session_group')) }} {num}</h4>
    <hr>
    <div class="row mb-4-5">
        <label for="group_max_ppl[{idx}]" class="col-sm-4 col-form-label text-md-end">{{ __('common.group_max_ppl_for') }}</label>

        <div class="col-md-6">
            <input id="group_max_ppl[{idx}]" type="number" value="10" min="2" max="65535" class="form-control" name="group_max_ppl[{idx}]" required>
        </div>
    </div>
    <div id="step3_{num}"></div>
</script>

<script type="text/template" id="msg_time">{{ __('common.time_end_before_start') }}</script>
<script type="text/template" id="msg_last_date_violation">{{ __('common.last_date_violation') }}</script>
<script type="text/template" id="msg_date_before_previous_date">{{ __('common.date_before_previous_date') }}</script>
<script type="text/template" id="msg_date_after_next_date">{{ __('common.date_after_next_date') }}</script>
<script type="text/template" id="msg_overlapping_sessions">{{ __('common.overlapping_sessions') }}</script>

<script type="text/template" id="group_inner_template">
    <div class="mb-3 row">
        <label for="session_location{suffix}" class="col-sm-4 col-form-label text-md-end">{{ __('common.session_location_for') }}{num}</label>

        <div class="col-md-6">
            <input id="session_location{suffix}" class="form-control" oninvalid="this.setCustomValidity('{{ __('validation.max_length_error', ['length' => 150]) }}')" oninput="this.setCustomValidity('')" pattern=".{0,150}" name="session_location{suffix}" required>
        </div>
    </div>
    <div class="mb-3 row">
        <label for="session_date{suffix}" class="col-sm-4 col-form-label text-md-end">{{ __('common.session_date_for') }}{num}</label>

        <div class="col-md-6">
            <input id="session_date{suffix}" type="date" class="form-control" name="session_date{suffix}" required>
        </div>
    </div>
    <div class="mb-3 row mb-4-5">
        <label for="session_starttime{suffix}" class="col-sm-4 col-form-label text-md-end">{{ __('common.session_time_for') }}{num}</label>

        <div class="col-md-3">
            <input type="time" id="session_starttime{suffix}" class="form-control" name="session_starttime{suffix}" required>
        </div>
        <div class="col-md-3">
            <input type="time" id="session_endtime{suffix}" class="form-control" name="session_endtime{suffix}" required>
        </div>
    </div>
</script>

<script type="text/template" id="header0">
    @svg('solid/circle-info') {{ __('common.course_info') }}
</script>

<script type="text/template" id="header1">
    @svg('solid/list') {{ __('common.descriptions') }}
</script>

<script type="text/template" id="header2">
    @svg('regular/calendar') {{ __('common.session_groups') }}
</script>

<div class="card">
    <div class="card-header" id="header">&nbsp;</div>
    <form method="POST" id="form" action="{{ route('course.store') }}">
        @csrf
        <div id="fieldset-carousel">
            <fieldset class="first" id="fieldset-1">
                <div class="card-body">
                    <div class="mb-3 row">
                        <label for="course_name" class="col-sm-4 col-form-label text-md-end">{{ __('common.course_name') }}</label>

                        <div class="col-md-6">
                            <input id="course_name" oninvalid="this.setCustomValidity('{{ __('validation.max_length_error', ['length' => 100]) }}')" oninput="this.setCustomValidity('')" pattern=".{0,100}" class="form-control" name="course_name" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="last_date" class="col-sm-4 col-form-label text-md-end">{{ __('common.last_subscribe_date') }}</label>

                        <div class="col-md-6">
                            <input id="last_date" type="date" class="form-control" name="last_date" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="times" class="col-sm-4 col-form-label text-md-end">{{ __('common.course_times_given') }}</label>

                        <div class="col-md-6">
                            <input id="times" type="number" value="1" min="1" max="10" class="form-control" name="times" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="session_count" class="col-sm-4 col-form-label text-md-end">{{ __('common.course_session_count') }}</label>

                        <div class="col-md-6">
                            <input id="session_count" type="number" value="1" min="1" max="10" class="form-control" name="session_count" required>
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
                            <textarea id="description" name="description" rows="7" class="form-control" required></textarea>
                        </div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-6 offset-md-4">
                            <button id="btnNext0" type="button" class="btn btn-primary">{{ __('pagination.next') }}</button>
                        </div>
                    </div>
                </div>
            </fieldset>
            <fieldset class="second" id="fieldset-2" disabled>
                <div class="card-body">
                    <div id="step2"></div>
                    <div class="row mb-0">
                        <div class="col-md-6 offset-md-4 btn-group">
                            <button id="btnPrev1" type="button" class="btn btn-outline-secondary">{{ __('pagination.previous') }}</button>
                            <button id="btnNext1" type="button" class="btn btn-primary">{{ __('pagination.next') }}</button>
                        </div>
                    </div>
                </div>
            </fieldset>
            <fieldset class="second" disabled>
                <div class="card-body">
                    <div id="step3"></div>
                    <div class="row mb-0">
                        <div class="col-md-6 offset-md-4 btn-group">
                            <button id="btnPrev2" type="button" class="btn btn-outline-secondary">{{ __('pagination.previous') }}</button>
                            <button id="btnCreate" type="submit" class="btn btn-primary">{{ __('acts.create') }}</button>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
    </form>
</div>
<script src="{{ url(mix('js/createcourse.js')) }}"></script>
@endsection
