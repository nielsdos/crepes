<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords
    {
        resetPassword as protected parentResetPassword;
    }

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected string $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password): void
    {
        $this->parentResetPassword($user, $password);

        // If you change your password using an e-mailed link, then you clearly have access to the e-mail account.
        // Therefore, interpret this as an indirect verification
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();

            if ($user->trashed()) {
                $user->restore();
                session()->flash('success', __('auth.restored_done'));
            }
        }
    }
}
