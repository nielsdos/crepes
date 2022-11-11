<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\ChangeEmailConfirmation;
use App\Notifications\PasswordChanged;
use App\Services\Exports\UsersExportable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    use ExportResponseCreator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        $this->middleware('can:index,App\Models\User')->except(['edit', 'updatePersonal', 'updatePassword', 'updateEmail', 'updateAdmin', 'destroy', 'forget']);
        $this->middleware('can:delete,App\Models\User')->only('destroy');
    }

    /**
     * @param  string|null  $q
     * @return array{0: ?string, 1: \Illuminate\Database\Eloquent\Builder<User>}
     */
    private function createUserQueryFromPotentialFilter(?string $q): array
    {
        if ($q) {
            $fullTextOptions = ['mode' => 'boolean'];

            return [$q, User::withTrashed()
                ->orWhereFullText('lastname', $q, $fullTextOptions)
                ->orWhereFullText('firstname', $q, $fullTextOptions)
                ->orWhereFullText('function', $q, $fullTextOptions)
                ->orWhereFullText('member_nr', $q, $fullTextOptions), ];
        } else {
            return [null, User::withTrashed()];
        }
    }

    /**
     * @param  Request  $request
     * @return array{0: ?string, 1: \Illuminate\Database\Eloquent\Builder<\App\Models\User>}
     */
    private function createUserQueryFromPotentialRequestFilter(Request $request): array
    {
        $q = $request->query('q');
        if (! is_string($q)) {
            $q = null;
        }

        return $this->createUserQueryFromPotentialFilter($q);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        [$q, $users] = $this->createUserQueryFromPotentialRequestFilter($request);

        if ($q) {
            session()->put('account_index_q', $q);
        } else {
            session()->forget('account_index_q');
        }

        if ($page = $request->query('page')) {
            session()->put('account_index_page', $page);
        } else {
            session()->forget('account_index_page');
        }

        $loadUsers = fn ($users) => $users->orderBy('lastname')->orderBy('firstname')->paginate(20);

        $hasSyntaxError = false;
        try {
            $users = $loadUsers($users);
        } catch (QueryException) {
            $users = User::withTrashed();
            $users = $loadUsers($users);
            session()->forget('account_index_q');
            $hasSyntaxError = true;
        }

        return view('account.index', compact('users', 'q', 'hasSyntaxError'));
    }

    /**
     * Internal export function.
     *
     * @param  ExportFileType  $exportFileType
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function exportInternal(ExportFileType $exportFileType): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        $users = $this->createUserQueryFromPotentialFilter(session()->get('account_index_q'))[1];
        $response = $this->createExportResponse(new UsersExportable($users), 'accounts', $exportFileType);

        return $response ?? redirect(route('account.index'))->with('fail', __('acts.export_failed'));
    }

    /**
     * Exports an excel file of the users.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Symfony\Component\HttpFoundation\BinaryFileResponse
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function exportExcel(): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
    {
        return $this->exportInternal(ExportFileType::Excel);
    }

    /**
     * Exports a CSV file of the users.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Symfony\Component\HttpFoundation\BinaryFileResponse
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function exportCSV(): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
    {
        return $this->exportInternal(ExportFileType::CSV);
    }

    /**
     * Transforms an Id
     *
     * @param  string  $id
     * @return string
     */
    private function transformId(string $id): string
    {
        return $id === 'me' ? Auth::id() : $id;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
     *
     * @throws AuthorizationException
     */
    public function edit(string $id): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', [User::class, $id]);

        $user = User::withTrashed()->find($this->transformId($id));
        if (! $user) {
            return $this->redirectAccountNotFound();
        }

        return view('account.edit', compact('user', 'id'));
    }

    private function createCurrentPasswordValidationCheck(User $user): callable
    {
        return function ($attribute, $value, $fail) use ($user) {
            if (! \Hash::check($value, $user->password)) {
                return $fail(__('auth.incorrect_current_password'));
            }
        };
    }

    /**
     * @throws AuthorizationException
     */
    private function updatePrelude(string $ability, string $id, bool $allowAdmins): User
    {
        $this->authorize($ability, [User::class, $id]);
        $id = $this->transformId($id);
        if (! $allowAdmins && Auth::id() != $id) {
            throw new AuthorizationException('No admins allowed');
        }

        return User::withTrashed()->findOrFail($id);
    }

    private function updateRedirectBack(Request $request, string $returnMsg, string $hash): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        if ($request->input('save')) {
            $route = redirect(
                Auth::user()->isAdmin() ? self::routeWithQuery('account.index') : url('/')
            );
        } else {
            $route = redirect()->to(url()->previous().'#'.$hash);
        }

        return $route->with('success', $returnMsg);
    }

    /**
     * @throws AuthorizationException
     */
    public function updatePersonal(Request $request, string $id): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $user = $this->updatePrelude('update', $id, true);

        $validated = $request->validate([
            'firstname' => 'required|string|max:80',
            'lastname' => 'required|string|max:80',
            'function' => 'nullable|string|max:50',
            'member_nr' => 'nullable|string|min:6|max:16',
        ]);

        $user->firstname = $validated['firstname'];
        $user->lastname = $validated['lastname'];
        $user->function = $validated['function'];
        $user->member_nr = $validated['member_nr'];
        $user->reminders = $request->has('reminders');
        $user->save();

        return $this->updateRedirectBack($request, __('acts.saved'), 'personal');
    }

    /**
     * @param  Request  $request
     * @param  array<string|array<string|Rule>>  $rules
     * @param  string  $hash
     * @return array<string|int>
     *
     * @throws ValidationException
     */
    private function validateRequestWithHashFailureRedirect(Request $request, array $rules, string $hash): array
    {
        try {
            return $request->validate($rules);
        } catch (ValidationException $exception) {
            $exception->redirectTo(url()->previous().'#'.$hash);
            throw $exception;
        }
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function updatePassword(Request $request, string $id): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $user = $this->updatePrelude('update', $id, false);

        $validated = $this->validateRequestWithHashFailureRedirect($request, [
            'password_current_password' => ['required', 'string', $this->createCurrentPasswordValidationCheck($user)],
            'password' => 'required|string|min:8|confirmed',
        ], 'password');

        $user->password = \Hash::make($validated['password']);
        $user->save();

        $user->notify(new PasswordChanged);

        return $this->updateRedirectBack($request, __('acts.saved'), 'password');
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function updateEmail(Request $request, string $id): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $user = $this->updatePrelude('update', $id, false);

        $validated = $this->validateRequestWithHashFailureRedirect($request, [
            'email_current_password' => ['required', 'string', $this->createCurrentPasswordValidationCheck($user)],
            'email' => 'required|string|email|max:255|confirmed|unique:users,email,'.$user->id,
        ], 'email');

        $email = $validated['email'];

        // Only change email if really changed...
        if ($user->email !== $email) {
            $url = URL::temporarySignedRoute(
                'verification.change.email',
                now()->addMinutes(60), [
                    'id' => $user->id,
                    'p' => \Crypt::encryptString($email),
                ]
            );

            $user->notify(new ChangeEmailConfirmation($url));

            $returnMsg = __('acts.email_change_msg', ['email' => $email]);
        } else {
            $returnMsg = __('acts.email_change_same');
        }

        return $this->updateRedirectBack($request, $returnMsg, 'email');
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function updateAdmin(Request $request, string $id): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $user = $this->updatePrelude('updateAdmin', $id, true);

        $validated = $this->validateRequestWithHashFailureRedirect($request, [
            'admin_email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'role' => ['required', 'integer', Rule::in(User::PERMS_ARRAY)],
        ], 'admin');

        $user->email = $validated['admin_email'];
        $user->perms = $validated['role'];

        if ($request->has('verifiedCheck')) {
            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }
        } else {
            $user->email_verified_at = null;
            if ($user->id === Auth::id()) {
                Auth::logout();
            }
        }

        $user->save();

        return $this->updateRedirectBack($request, __('acts.saved'), 'admin');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User  $user
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
     */
    public function destroy(User $user): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $email = $user->email;

        if (Auth::id() !== $user->id) {
            $user->forceDelete();

            return redirect(self::routeWithQuery('account.index'))
                    ->with('success', __('acts.account_removed', ['account' => $email]));
        }

        return redirect(url('/'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Throwable
     */
    public function forget(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        \DB::transaction(function () use ($user) {
            $user->email_verified_at = null;
            $user->save();
            $user->delete();
        });

        return redirect(url('/'));
    }

    /**
     * Resolve route with query string.
     *
     * @param  string  $dest
     * @return string
     */
    public static function routeWithQuery(string $dest): string
    {
        return route($dest,
            array_filter([
                'q' => session()->get('account_index_q'),
                'page' => session()->get('account_index_page'),
            ]));
    }

    /**
     * Redirect to account index with "account not found" message.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function redirectAccountNotFound(): \Illuminate\Http\RedirectResponse
    {
        return redirect(self::routeWithQuery('account.index'))
                ->with('fail', __('acts.account_not_found'));
    }
}
