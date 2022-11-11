<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Subscription
 *
 * @property int $id
 * @property int $user_id
 * @property int $session_group_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\SessionGroup $sessionGroup
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription newQuery()
 * @method static \Illuminate\Database\Query\Builder|Subscription onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription query()
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription whereSessionGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Subscription withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Subscription withoutTrashed()
 * @mixin \Eloquent
 *
 * @method static \Database\Factories\SubscriptionFactory factory(...$parameters)
 */
class Subscription extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['user_id', 'session_group_id'];

    /**
     * @var string[]
     */
    protected $dates = ['created_at'];

    /**
     * @return BelongsTo<\App\Models\SessionGroup, \App\Models\Subscription>
     */
    public function sessionGroup(): BelongsTo
    {
        return $this->belongsTo('App\Models\SessionGroup');
    }

    /**
     * @return BelongsTo<\App\Models\User, \App\Models\Subscription>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User')->orderBy('lastname')->orderBy('firstname');
    }

    public function groupIndex(): int
    {
        $idOff = $this->sessionGroup->course->sessionGroups->first()->id - 1;

        return $this->session_group_id - $idOff;
    }
}
