@extends('layouts.app')

@section('title', __('acts.edit_account'))
@section('titleicon')
@svg('solid/user')
@endsection
@section('content')
@if(Auth::user()->isAdmin())
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('account.index') }}">{{ __('acts.user_management') }}</a></li>
        @if(session('account_index_q'))
        <li class="breadcrumb-item"><a href="{{ \App\Http\Controllers\AccountController::routeWithQuery('account.index') }}">{{ __('acts.search_results_for', ['for' => session('account_index_q')]) }}</a></li>
        @endif
        <li class="breadcrumb-item active" aria-current="page">{{ __('acts.edit_account_param', ['account' => $user->email]) }}</li>
    </ol>
</nav>
@endif
@include('partials.messages')
<div class="modal fade" id="deactivateModal" tabindex="-1" role="dialog" aria-labelledby="deactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deactivateModalLabel">{{ __('acts.deactivate_account') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    {{ __('acts.deactivate_account_confirmation') }}
                </p>
            </div>
            <div class="modal-footer">
                <form method="post" action="{{ route('account.forget') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger">{{ __('common.yes') }}</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.no') }}</button>
            </div>
        </div>
    </div>
</div>
@php
$navbarPillsHeaderEntries = [
    ['personal', @svg("solid/user")->toHtml().' '.__('common.personal_data')],
];
if (Auth::id() === $user->id) {
    $navbarPillsHeaderEntries[] = ['password', @svg("solid/lock")->toHtml().' '.__('common.password')];
    $navbarPillsHeaderEntries[] = ['email', @svg("solid/envelope")->toHtml().' '.__('common.email')];
    $navbarPillsHeaderEntries[] = ['deactivate-account', @svg("solid/toggle-off")->toHtml().' '.__('acts.deactivate_account')];
}
if (Auth::user()->isAdmin()) {
    $navbarPillsHeaderEntries[] = ['admin', @svg("solid/wand-magic-sparkles")->toHtml().' '.__('acts.admin_section')];
}
@endphp
@include('partials.navbar-pills-header', ['entries' => $navbarPillsHeaderEntries])
<div class="card">
    <div class="tab-content">
        <div class="card-body tab-pane" role="tabpanel" aria-labelledby="personal-tab" id="personal">
            <form method="POST" action="{{ route('account.update.personal', $id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3 row">
                    <label for="firstname" class="col-md-4 col-form-label text-md-end">{{ __('common.firstname') }}</label>

                    <div class="col-md-6">
                        <input id="firstname" type="text" class="form-control{{ $errors->has('firstname') ? ' is-invalid' : '' }}" name="firstname" value="{{ old_str('firstname', $user->firstname) }}" required>

                        @if($errors->has('firstname'))
                            <span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('firstname')) }}</span>
                        @endif
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="lastname" class="col-md-4 col-form-label text-md-end">{{ __('common.lastname') }}</label>

                    <div class="col-md-6">
                        <input id="lastname" type="text" class="form-control{{ $errors->has('lastname') ? ' is-invalid' : '' }}" name="lastname" value="{{ old_str('lastname', $user->lastname) }}" required>

                        @if($errors->has('lastname'))
                            <span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('lastname')) }}</span>
                        @endif
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="function" class="col-md-4 col-form-label text-md-end">{{ __('common.function') }}</label>

                    <div class="col-md-6">
                        <input id="function" type="text" class="form-control{{ $errors->has('function') ? ' is-invalid' : '' }}" name="function" value="{{ old_str('function', $user->function) }}">

                        @if($errors->has('function'))
                            <span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('function')) }}</span>
                        @endif
                        <span class="text-secondary small-msg">{{ __('auth.function_example') }}, &hellip;</span>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="member_nr" class="col-md-4 col-form-label text-md-end">{{ __('common.member_nr') }}</label>

                    <div class="col-md-6">
                        <input id="member_nr" type="text" maxlength="20" class="form-control{{ $errors->has('member_nr') ? ' is-invalid' : '' }}" name="member_nr" value="{{ old_str('member_nr', $user->member_nr) }}">

                        @if($errors->has('member_nr'))
                            <span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('member_nr')) }}</span>
                        @endif
                        <span class="text-secondary small-msg">{{ __('common.member_nr_info') }}</span>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label text-md-end pt-0" for="verifiedCheck">{{ __('common.send_reminders') }}</label>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" aria-label="{{ __('common.send_reminders') }}" type="checkbox" name="reminders" id="reminders"{{ $user->reminders ? ' checked' : '' }}>
                        </div>
                        <span class="text-secondary small-msg">{{ __('common.send_reminders_info') }}</span>
                    </div>
                </div>

                <div class="row mb-0">
                    <div class="col-md-6 offset-md-4 btn-group">
                        <button type="submit" name="save" value="close" class="btn btn-primary">
                            {{ __('acts.save_and_back') }}
                        </button>
                        <button type="submit" name="save" class="btn btn-outline-primary">
                            {{ __('acts.save') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @if(Auth::id() === $user->id)
    <div class="tab-content">
        <div class="card-body tab-pane" role="tabpanel" aria-labelledby="password-tab" id="password">
            <p>
                {{ __('acts.password_change_instructions') }}
                <br>
                {{ __('acts.password_change_instructions_2') }}
            </p>
            <form method="POST" action="{{ route('account.update.password', $id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3 row">
                    <label for="password_current_password" class="col-md-4 col-form-label text-md-end">{{ __('auth.old_password') }}</label>

                    <div class="col-md-6">
                        <input id="password_current_password" type="password" class="form-control{{ $errors->has('password_current_password') ? ' is-invalid' : '' }}" name="password_current_password" required>

                        @if($errors->has('password_current_password'))
                            <span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('password_current_password')) }}</span>
                        @endif
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('auth.new_password') }}</label>

                    <div class="col-md-6">
                        <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                        @if($errors->has('password'))
                            <span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('password')) }}</span>
                        @endif
                        <span class="text-secondary small-msg">{{ __('auth.password_info') }}</span>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('auth.new_password_confirm') }}</label>

                    <div class="col-md-6">
                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                    </div>
                </div>

                <div class="row mb-0">
                    <div class="col-md-6 offset-md-4 btn-group">
                        <button type="submit" name="save" value="close" class="btn btn-primary">
                            {{ __('acts.save_and_back') }}
                        </button>
                        <button type="submit" name="save" class="btn btn-outline-primary">
                            {{ __('acts.save') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="tab-content">
        <div class="card-body tab-pane" role="tabpanel" aria-labelledby="email-tab" id="email">
            <p>
                {{ __('acts.email_change_instructions') }}
                <br>
                {{ __('acts.email_change_instructions_2') }}
            </p>
            <form method="POST" action="{{ route('account.update.email', $id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3 row">
                    <label for="email_current_password" class="col-md-4 col-form-label text-md-end">{{ __('auth.old_password') }}</label>

                    <div class="col-md-6">
                        <input id="email_current_password" type="password" class="form-control{{ $errors->has('email_current_password') ? ' is-invalid' : '' }}" name="email_current_password" required>

                        @if($errors->has('email_current_password'))
                            <span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('email_current_password')) }}</span>
                        @endif
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('auth.new_email') }}</label>

                    <div class="col-md-6">
                        <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" required>

                        @if($errors->has('email'))
                            <span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('email')) }}</span>
                        @endif
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="email-confirm" class="col-md-4 col-form-label text-md-end">{{ __('auth.new_email_confirm') }}</label>

                    <div class="col-md-6">
                        <input id="email-confirm" type="email" class="form-control" name="email_confirmation" required>
                    </div>
                </div>

                <div class="row mb-0">
                    <div class="col-md-6 offset-md-4 btn-group">
                        <button type="submit" name="save" value="close" class="btn btn-primary">
                            {{ __('acts.save_and_back') }}
                        </button>
                        <button type="submit" name="save" class="btn btn-outline-primary">
                            {{ __('acts.save') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="tab-content">
        <div class="card-body tab-pane" role="tabpanel" aria-labelledby="deactivate-account-tab" id="deactivate-account">
            <p>{{ __('acts.right_to_forget') }}</p>
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deactivateModal">{{ __('acts.deactivate_account') }}</button>
        </div>
    </div>
    @endif
    @if(Auth::user()->isAdmin())
    <div class="tab-content">
        <div class="card-body tab-pane" role="tabpanel" aria-labelledby="admin-tab" id="admin">
            <p>
                {{ __('acts.admin_section_instructions') }}
                <br>
                {{ __('acts.admin_section_instructions_2') }}
            </p>
            <form method="POST" action="{{ route('account.update.admin', $id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3 row">
                    <label for="admin_email" class="col-md-4 col-form-label text-md-end">{{ __('common.email') }}</label>

                    <div class="col-md-6">
                        <input id="admin_email" type="email" value="{{ $user->email }}" class="form-control{{ $errors->has('admin_email') ? ' is-invalid' : '' }}" name="admin_email" required>

                        @if($errors->has('admin_email'))
                            <span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('admin_email')) }}</span>
                        @endif
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label text-md-end">{{ __('common.role') }}</label>
                    <input type="hidden" id="role" name="role" value="{{ $user->perms }}">

                    <div class="col-md-6">
                        <div class="dropdown">
                            <a class="btn btn-light dropdown-toggle" href="#" role="button" id="dropdownRoles" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ __('common.role-'.$user->perms) }}
                            </a>

                            <ul class="dropdown-menu" id="roleMenu" aria-labelledby="dropdownRoles">
                                @foreach(\App\Models\User::PERMS_ARRAY as $role)
                                    <li><a class="dropdown-item" href="#" data-role="{!! $role !!}">{{ __('common.role-'.$role) }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <script>(function(){function handle(e){document.getElementById('role').value=e.target.dataset.role;document.getElementById('dropdownRoles').innerText=e.target.innerText+' '}var tags=document.getElementById('roleMenu').getElementsByTagName('a');var i=0;for(;i<tags.length;i++){tags[i].addEventListener('click',handle,!1)}})()</script>

                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label text-md-end" for="verifiedCheck">{{ __('common.verified') }}</label>
                    <div class="col-md-6 mt-auto mb-auto">
                        <input class="form-check-input" aria-label="{{ __('common.verified') }}" type="checkbox" name="verifiedCheck" id="verifiedCheck" @checked($user->hasVerifiedEmail())>
                    </div>
                </div>

                <div class="row mb-0">
                    <div class="col-md-6 offset-md-4 btn-group">
                        <button type="submit" name="save" value="close" class="btn btn-primary">
                            {{ __('acts.save_and_back') }}
                        </button>
                        <button type="submit" name="save" class="btn btn-outline-primary">
                            {{ __('acts.save') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
