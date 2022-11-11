<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Course
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property \Illuminate\Support\Carbon $last_date
 * @property int $owner_id
 * @property bool $notify_me
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User $owner
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SessionGroup[] $sessionGroups
 * @property-read int|null $session_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Subscription[] $subscriptions
 * @property-read int|null $subscriptions_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Course newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Course newQuery()
 * @method static \Illuminate\Database\Query\Builder|Course onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Course query()
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereLastDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereNotifyMe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Course withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Course withoutTrashed()
 * @mixin \Eloquent
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Session[] $sessions
 * @property-read int|null $sessions_count
 *
 * @method static \Database\Factories\CourseFactory factory(...$parameters)
 */
class Course extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = ['title', 'slug', 'description', 'last_date', 'owner_id', 'notify_me'];

    /**
     * @var string[]
     */
    protected $dates = ['last_date'];

    /**
     * @return HasMany<\App\Models\SessionGroup>
     */
    public function sessionGroups(): HasMany
    {
        return $this->hasMany('App\Models\SessionGroup')->orderBy('id');
    }

    /**
     * @return HasManyThrough<\App\Models\Session>
     */
    public function sessions(): HasManyThrough
    {
        return $this->hasManyThrough('App\Models\Session', 'App\Models\SessionGroup');
    }

    /**
     * @return HasManyThrough<\App\Models\Subscription>
     */
    public function subscriptions(): HasManyThrough
    {
        return $this->hasManyThrough('App\Models\Subscription', 'App\Models\SessionGroup');
    }

    /**
     * @return BelongsTo<\App\Models\User, \App\Models\Course>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'owner_id');
    }

    public function tooLateToSubscribe(): bool
    {
        return $this->last_date->addDay()->isPast();
    }
}
