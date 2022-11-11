<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\SessionGroup;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\AdminSubscribe;
use App\Notifications\OwnerSubscribe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class SubscriptionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);

        // Avoid email spam abuse
        $this->middleware('throttle:5,10')->only('unsubscribe');
    }

    /**
     * Gets the subscriptions (helper).
     *
     * @param  User  $user
     * @param  bool  $withTrashed
     * @return mixed
     */
    private static function getSubs(User $user, bool $withTrashed)
    {
        $x = $user->subscriptions();
        if ($withTrashed) {
            $x = $x->withTrashed();
        }

        return $x->with(['sessionGroup.course' => function ($q) {
            $q->select('title', 'id', 'slug');
        }, 'sessionGroup.course.sessionGroups' => function ($q) {
            $q->select('id', 'course_id');
        }])
         ->orderBy('created_at', 'desc')
         ->paginate(20);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): \Illuminate\Contracts\View\View
    {
        $subs = self::getSubs(Auth::user(), false);

        return view('subscriptions.index', ['subscriptions' => $subs, 'adminView' => false, 'showStatus' => Auth::user()->isAdmin()]);
    }

    /**
     * Displays a listing of the resource.
     *
     * @param  string  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show(string $id): \Illuminate\Contracts\View\View
    {
        if (! Auth::user()->isAdmin()) {
            abort(404);
        }

        $user = User::withTrashed()->select(['id', 'email'])->findOrFail($id);
        $subscriptions = self::getSubs($user, true);
        $adminView = true;
        $showStatus = Auth::user()->isAdmin();

        return view('subscriptions.index', compact('subscriptions', 'user', 'adminView', 'showStatus'));
    }

    /**
     * Notify people of subscription update (subscribe or unsubscribe)
     */
    private function notifySubscriptionUpdate(User $user, Course $course, int $groupIndex, bool $unsub): void
    {
        if ($course->notify_me) {
            $course->owner->notify(new OwnerSubscribe($user, $course, $groupIndex, $unsub));
        }

        Notification::route('mail', config('mail.notification_address'))
                        ->notify(new AdminSubscribe($user, $course, $groupIndex, $unsub));
    }

    /**
     * Subscribes a user to a course session group.
     *
     * @param  SessionGroup  $sessionGroup
     * @return RedirectResponse
     */
    public function subscribe(SessionGroup $sessionGroup)
    {
        $course = $sessionGroup->course;
        $user = Auth::user();

        if ($course->tooLateToSubscribe()               // Too late
            || $sessionGroup->isFull()                  // Full group
            || $user->subscriptionFor($course->id)      // Already subscribed
            || $user->id === $course->owner_id) {       // Is owner, so subscribing doesn't make sense
            return back();
        }

        $sub = Subscription::create([
            'user_id' => $user->id,
            'session_group_id' => $sessionGroup->id,
        ]);

        $this->notifySubscriptionUpdate($user, $course, $sub->groupIndex(), false);

        return back();
    }

    /**
     * Unsubscribes a user from a course session group.
     *
     * @param  Subscription  $subscription
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unsubscribe(Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()
            || $subscription->sessionGroup->course->tooLateToSubscribe()) {
            return back();
        }

        $this->notifySubscriptionUpdate(
            Auth::user(), $subscription->sessionGroup->course, $subscription->groupIndex(), true
        );

        $subscription->forceDelete();

        return back()->with('success', __('acts.unsubscribed'));
    }
}
