@extends('layouts.app')
@section('title', __('acts.settings_management'))
@section('titleicon')
@svg('solid/gear')
@endsection
@section('content')
    @include('partials.messages')

    @include('partials.navbar-pills-header', ['entries' => [
        ['view', @svg("solid/eye")->toHtml().' '.__('acts.view_config')],
        ['privacy-policy', @svg("solid/user-secret")->toHtml().' '.'Privacy policy'],
        ['options', @svg("solid/wrench")->toHtml().' '.__('acts.options')],
    ]])

    <div class="card">
        <div class="tab-content">
            <div role="tabpanel" class="card-body tab-pane" aria-labelledby="view-tab" id="view">
                <form method="POST" action="{{ route('settings.update.view') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3 row">
                        <label for="course_start_month" class="col-md-4 col-form-label text-md-end">{{ __('acts.course_start_month') }}</label>

                        <div class="col-md-6">
                            <select id="course_start_month" name="course_start_month"
                                    class="form-control form-select{{ $errors->has('course_start_month') ? ' is-invalid' : '' }}">
                                @foreach(range(1, 12) as $month)
                                    <option value="{{ $month }}" @selected(old('course_start_month', $course_start_month) === $month)>{{ __("common.month_{$month}") }}</option>
                                @endforeach
                            </select>

                            @if($errors->has('course_start_month'))
                                <span class="invalid-feedback"
                                      role="alert">{{ ucfirst($errors->first('course_start_month')) }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="course_overlap_months"
                               class="col-md-4 col-form-label text-md-end">{{ __('acts.course_overlap_months') }}</label>

                        <div class="col-md-6">
                            <select id="course_overlap_months" name="course_overlap_months"
                                    class="form-control form-select{{ $errors->has('course_overlap_months') ? ' is-invalid' : '' }}">
                                @foreach(range(0, 11) as $amount)
                                    <option value="{{ $amount }}" @selected(old('course_overlap_months', $course_overlap_months) === $amount)>{{ $amount }}</option>
                                @endforeach
                            </select>

                            <span class="text-secondary small-msg">{{ __('acts.settings_course_overlap_months_hint') }}</span>

                            @if($errors->has('course_overlap_months'))
                                <span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('course_overlap_months')) }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label text-md-end" for="show_map_on_course_details">{{ __('acts.show_map_on_course_details') }}</label>
                        <div class="col-md-6 mt-auto mb-auto">
                            <input class="form-check-input" aria-label="{{ __('common.show_map_on_course_details') }}" type="checkbox" name="show_map_on_course_details" id="show_map_on_course_details" @checked(old('show_map_on_course_details', $show_map_on_course_details))>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="main_meta_description"
                               class="col-md-4 col-form-label text-md-end">{{ __('acts.main_meta_description') }}</label>

                        <div class="col-md-6">
                            <input id="main_meta_description" type="text" class="form-control{{ $errors->has('main_meta_description') ? ' is-invalid' : '' }}" name="main_meta_description" value="{{ old_str('main_meta_description', $main_meta_description) }}">

                            <span class="text-secondary small-msg">{{ __('acts.main_meta_description_hint') }}</span>

                            @if($errors->has('main_meta_description'))
                            <span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('main_meta_description')) }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-6 offset-md-4">
                            <button type="submit" name="save" class="btn btn-primary">
                                {{ __('acts.save') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div role="tabpanel" class="card-body tab-pane" aria-labelledby="privacy-policy-tab" id="privacy-policy">
                <form method="POST" action="{{ route('settings.update.privacy') }}">
                    @csrf
                    @method('PUT')

                    <textarea name="privacy_policy_html" id="editor">
                        {!! $privacy_policy_html !!}
                    </textarea>

                    <button type="submit" name="save" class="btn btn-primary mt-3">
                        {{ __('acts.save') }}
                    </button>
                </form>
            </div>
            <div role="tabpanel" class="card-body tab-pane" aria-labelledby="options-tab" id="options">
                <form method="POST" action="{{ route('settings.update.options') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3 row">
                        <label for="admin_notification_email"
                               class="col-md-4 col-form-label text-md-end">{{ __('acts.admin_notification_email') }}</label>

                        <div class="col-md-6">
                            <input id="admin_notification_email" type="text" class="form-control{{ $errors->has('admin_notification_email') ? ' is-invalid' : '' }}" name="admin_notification_email" value="{{ old_str('admin_notification_email', $admin_notification_email) }}">

                            <span class="text-secondary small-msg">{{ __('acts.admin_notification_email_hint') }}</span>

                            @if($errors->has('admin_notification_email'))
                            <span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('admin_notification_email')) }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-6 offset-md-4">
                            <button type="submit" name="save" class="btn btn-primary">
                                {{ __('acts.save') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ url(mix('js/editor.js')) }}"></script>
@endsection
