<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\SessionGroup
 *
 * @property int $id
 * @property int $course_id
 * @property int $max_ppl
 * @property-read \App\Models\Course $course
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Session[] $sessions
 * @property-read int|null $sessions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Subscription[] $subscriptions
 * @property-read int|null $subscriptions_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SessionGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SessionGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SessionGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|SessionGroup whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SessionGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SessionGroup whereMaxPpl($value)
 * @mixin \Eloquent
 *
 * @method static \Database\Factories\SessionGroupFactory factory(...$parameters)
 */
class SessionGroup extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['course_id', 'max_ppl'];

    /**
     * @return HasMany<\App\Models\Session>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany('App\Models\Session')->orderBy('id');
    }

    /**
     * @return HasMany<\App\Models\Subscription>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany('App\Models\Subscription');
    }

    /**
     * @return BelongsTo<\App\Models\Course, \App\Models\SessionGroup>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course');
    }

    public function isFull(): bool
    {
        return $this->subscriptions->count() >= $this->max_ppl;
    }
}
