@extends('layouts.app')
@section('title', __('acts.login'))
@section('content')
<div class="row justify-content-center">
	<div class="col-md-8">
		<div class="card">
			<div class="card-header">@svg('solid/arrow-right-to-bracket') {{ __('acts.login') }}</div>

			<div class="card-body">
				<form method="POST" action="{{ route('login') }}">
					@csrf

					<div class="mb-3 row">
						<label for="email" class="col-sm-4 col-form-label text-md-end">{{ __('common.email') }}</label>

						<div class="col-md-6">
							<input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old_str('email') }}" required autofocus>

							@if($errors->has('email'))
								<span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('email')) }}</span>
							@endif
						</div>
					</div>

					<div class="mb-3 row">
						<label for="password" class="col-md-4 col-form-label text-md-end">{{ __('auth.password') }}</label>

						<div class="col-md-6">
							<input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

							@if($errors->has('password'))
								<span class="invalid-feedback" role="alert">{{ ucfirst($errors->first('password')) }}</span>
							@endif
						</div>
					</div>

					<div class="mb-3 row">
						<div class="col-md-6 offset-md-4">
							<div class="form-check">
								<input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

								<label class="form-check-label" for="remember">
									{{ __('auth.remember_me') }}
								</label>
								<br>
								<span class="text-secondary small-msg">{{ __('auth.remember_me_cookie_notice') }}</span>
							</div>
						</div>
					</div>

					<div class="row mb-0">
						<div class="col-md-8 offset-md-4">
							<button type="submit" class="btn btn-primary">
								{{ __('acts.login') }}
							</button>

							<a class="btn btn-link" href="{{ route('password.request') }}">
								{{ __('auth.forgot_password') }}
							</a>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection
