<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $firstname
 * @property string $lastname
 * @property string|null $function
 * @property int $perms
 * @property string|null $member_nr
 * @property string $email
 * @property string|null $email_verified_at
 * @property string $password
 * @property bool $reminders
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Course[] $courses
 * @property-read int|null $courses_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Subscription[] $subscriptions
 * @property-read int|null $subscriptions_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Query\Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFunction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMemberNr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereReminders($value)
 * @method static \Illuminate\Database\Query\Builder|User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|User withoutTrashed()
 * @mixin \Eloquent
 *
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;
    use SoftDeletes;
    use HasFactory;

    public $timestamps = false;

    const PERMS_USER = 5;

    const PERMS_COURSE_MANAGER = 10;

    const PERMS_ADMIN = 255;

    const PERMS_ARRAY = [self::PERMS_USER, self::PERMS_COURSE_MANAGER, self::PERMS_ADMIN];

    protected $fillable = [
        'firstname', 'lastname', 'function', 'member_nr', 'email', 'password', 'perms', 'reminders', 'email_verified_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];

    /**
     * Returns true if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->perms >= self::PERMS_ADMIN;
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * Gets the full name.
     *
     * @return string
     */
    public function fullName(): string
    {
        return $this->firstname.' '.$this->lastname;
    }

    /**
     * @return HasMany<\App\Models\Subscription>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany('App\Models\Subscription');
    }

    /**
     * @return HasMany<\App\Models\Course>
     */
    public function courses(): HasMany
    {
        return $this->hasMany('App\Models\Course', 'owner_id', 'id');
    }

    /**
     * Gets the subscription for this user and course combination.
     *
     * @param  int  $courseId
     * @return ?Subscription
     */
    public function subscriptionFor(int $courseId): ?Subscription
    {
        return $this->subscriptions()
                    ->join('session_groups', 'session_group_id', '=', 'session_groups.id')
                    ->where('course_id', $courseId)
                    ->select('subscriptions.*')
                    ->first();
    }
}
