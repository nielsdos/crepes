@extends('layouts.app')
@section('title', __('auth.verify_your_email'))
@section('content')
<div class="row justify-content-center">
	<div class="col-md-8">
		<div class="card">
			<div class="card-header">@svg('solid/envelope') {{ __('auth.verify_your_email') }}</div>

			<div class="card-body">
                @if(session('resent'))
                    <div class="alert alert-success" role="alert">
                        {{ __('auth.verification_link_sent') }}
                    </div>
                @endif
                @if(session('restored'))
                    <div class="alert alert-success" role="alert">
                        {{ __('auth.restored_notice') }}
                    </div>
                @endif

				{{ __('auth.before_proceed_validate_email') }}
                <br>
                {{ __('auth.not_receive_verification_mail_1') }}
                <form action="{{ route('verification.resend') }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary mt-3">{{ __('auth.not_receive_verification_mail_2') }}</button>
                </form>
			</div>
		</div>
	</div>
</div>
@endsection
