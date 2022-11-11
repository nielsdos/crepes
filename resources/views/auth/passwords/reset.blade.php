@extends('layouts.app')
@section('title', __('auth.forgot_password'))
@section('content')
<div class="row justify-content-center">
	<div class="col-md-8">
		<div class="card">
			<div class="card-header">@svg('solid/lock') {{ __('auth.forgot_password') }}</div>

			<div class="card-body">
				<form method="POST" action="{{ route('password.update') }}">
					@csrf

					<input type="hidden" name="token" value="{{ $token }}">

					<div class="mb-3 row">
						<label for="email" class="col-md-4 col-form-label text-md-end">{{ __('common.email') }}</label>

						<div class="col-md-6">
							<input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ $email ?? old_str('email') }}" required autofocus>

							@if($errors->has('email'))
								<span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('email')) }}</span>
							@endif
						</div>
					</div>

					<div class="mb-3 row">
						<label for="password" class="col-md-4 col-form-label text-md-end">{{ __('common.password') }}</label>

						<div class="col-md-6">
							<input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

							@if($errors->has('password'))
								<span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('password')) }}</span>
							@endif
						</div>
					</div>

					<div class="mb-3 row">
						<label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('auth.password_confirm') }}</label>

						<div class="col-md-6">
							<input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
						</div>
					</div>

					<div class="row mb-0">
						<div class="col-md-6 offset-md-4">
							<button type="submit" class="btn btn-primary">
								{{ __('Reset Password') }}
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection
