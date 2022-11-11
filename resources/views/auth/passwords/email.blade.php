@extends('layouts.app')
@section('title', __('auth.forgot_password'))
@section('content')
<div class="row justify-content-center">
	<div class="col-md-8">
		<div class="card">
			<div class="card-header">@svg('solid/lock') {{ __('auth.forgot_password') }}</div>

			<div class="card-body">
				@if(session('status'))
					<div class="alert alert-success" role="alert">
						{{ session('status') }}
					</div>
				@endif

				<form method="POST" action="{{ route('password.email') }}">
					@csrf

					<div class="mb-3 row">
						<label for="email" class="col-md-4 col-form-label text-md-end">{{ __('common.email') }}</label>

						<div class="col-md-6">
							<input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old_str('email') }}" required>

							@if($errors->has('email'))
								<span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('email')) }}</span>
							@endif
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
							<button type="submit" class="btn btn-primary">
								{{ __('auth.send_password_reset_link') }}
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection
