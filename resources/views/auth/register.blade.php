@extends('layouts.app')
@section('title', __('acts.register'))
@section('content')
<div class="row justify-content-center">
	<div class="col-md-8">
		<div class="card">
			<div class="card-header">@svg('solid/user-plus') {{ __('acts.register') }}</div>

			<div class="card-body">
				<form method="POST" action="{{ route('register') }}">
					@csrf

					<div class="mb-3 row">
						<label for="firstname" class="col-md-4 col-form-label text-md-end">* {{ __('common.firstname') }}</label>

						<div class="col-md-6">
							<input id="firstname" type="text" class="form-control{{ $errors->has('firstname') ? ' is-invalid' : '' }}" name="firstname" value="{{ old_str('firstname') }}" required autofocus>

							@if($errors->has('firstname'))
								<span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('firstname')) }}</span>
							@endif
						</div>
					</div>

					<div class="mb-3 row">
						<label for="lastname" class="col-md-4 col-form-label text-md-end">* {{ __('common.lastname') }}</label>

						<div class="col-md-6">
							<input id="lastname" type="text" class="form-control{{ $errors->has('lastname') ? ' is-invalid' : '' }}" name="lastname" value="{{ old_str('lastname') }}" required>

							@if($errors->has('lastname'))
								<span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('lastname')) }}</span>
							@endif
						</div>
					</div>

					<div class="mb-3 row">
						<label for="email" class="col-md-4 col-form-label text-md-end">* {{ __('common.email') }}</label>

						<div class="col-md-6">
							<input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old_str('email') }}" required>

							@if($errors->has('email'))
								<span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('email')) }}</span>
							@endif
						</div>
					</div>

					<div class="mb-3 row">
						<label for="password" class="col-md-4 col-form-label text-md-end">* {{ __('auth.password') }}</label>

						<div class="col-md-6">
							<input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

							@if($errors->has('password'))
								<span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('password')) }}</span>
							@endif
							<span class="text-secondary small-msg">{{ __('auth.password_info') }}</span>
						</div>
					</div>

					<div class="mb-3 row">
						<label for="password-confirm" class="col-md-4 col-form-label text-md-end">* {{ __('auth.password_confirm') }}</label>

						<div class="col-md-6">
							<input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
						</div>
					</div>

					<div class="mb-3 row">
						<label for="function" class="col-md-4 col-form-label text-md-end">{{ __('common.function') }}</label>

						<div class="col-md-6">
							<input id="function" type="text" class="form-control{{ $errors->has('function') ? ' is-invalid' : '' }}" name="function" value="{{ old_str('function') }}">

							@if($errors->has('function'))
								<span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('function')) }}</span>
							@endif
							<span class="text-secondary small-msg">{{ __('auth.function_example') }}, &hellip;</span>
						</div>
					</div>

					<div class="mb-3 row">
						<label for="member_nr" class="col-md-4 col-form-label text-md-end">{{ __('common.member_nr') }}</label>

						<div class="col-md-6">
							<input id="member_nr" type="text" maxlength="20" class="form-control{{ $errors->has('member_nr') ? ' is-invalid' : '' }}" name="member_nr" value="{{ old_str('member_nr') }}">

							@if($errors->has('member_nr'))
								<span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('member_nr')) }}</span>
							@endif
							<span class="text-secondary small-msg">{{ __('common.member_nr_info') }}</span>
						</div>
					</div>

					<div class="mb-3 row">
						<label for="reminders" class="col-md-4 col-form-label text-md-end pt-0">{{ __('common.send_reminders') }}</label>

						<div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" aria-label="{{ __('common.send_reminders') }}" type="checkbox" name="reminders" id="reminders">
                            </div>
							<span class="text-secondary small-msg">{{ __('common.send_reminders_info') }} {{ __('common.you_can_still_change_this_later') }}</span>
						</div>
					</div>

					<div class="mb-3 row">
						<div class="col-md-4"></div>
						<div class="col-md-6">
							{!! NoCaptcha::renderJs(app()->getLocale()) !!}
							{!! NoCaptcha::display() !!}
							<span class="text-secondary small-msg">{{ __('auth.recaptcha_cookie_notice') }}</span>

							@if($errors->has('g-recaptcha-response'))
								<span class="invalid-feedback d-block" role="alert">{{ $errors->first('g-recaptcha-response') }}</span>
							@endif
						</div>
					</div>

					<div class="row mb-0">
						<div class="col-md-6 offset-md-4">
							<span class="text-secondary small-msg">{{ __('common.required_fields') }}</span>
							<br>
							<button type="submit" class="btn btn-primary">
								{{ __('acts.register') }}
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection
