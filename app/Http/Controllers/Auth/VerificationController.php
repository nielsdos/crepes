<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ChangeEmailOld;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
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
        $this->middleware('auth');
        $this->middleware('signed')->only(['change', 'verify']);
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    protected function verified(Request $request): void
    {
        if ($request->session()->pull('restored')) {
            $request->session()->flash('success', __('auth.restored_done'));
        }
    }

    /**
     * Mark the authenticated user's new email address as valid.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function change(Request $request, int $id): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $email = $request->query('p');
        if ($id !== $user->id || ! is_string($email)) {
            return redirect(url('/'));
        }

        try {
            $email = \Crypt::decryptString($email);
        } catch(DecryptException $e) {
            // This exception does not result in a padding oracle.
            // That's because this route is signed, therefore manipulating the query string to get an invalid encrypted
            // email parameter will fail the signature check.
            // This should not happen, if it does then it is a programmer error.
            \Log::error($e);

            return redirect(url('/'));
        }

        $dest = redirect(route('account.edit', ['user' => 'me']).'#email');

        // Re-check, but the real TOC-TOU is prevented by the unique constraint in the database.
        if (User::where('email', '=', $email)->exists()) {
            return $dest->with('fail', __('acts.email_already_used'));
        }

        $oldEmail = $user->email;
        $user->email = $email;
        $user->save();

        // Note: can't use $user->notify, even before the save, because by the time the queue runs, the email is already changed.
        Notification::route('mail', $oldEmail)->notify(new ChangeEmailOld($user->fullName(), $email));

        return $dest->with('success', __('acts.email_has_changed'));
    }
}
