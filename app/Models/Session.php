<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Session
 *
 * @property int $id
 * @property int $session_group_id
 * @property int $session_description_id
 * @property string $location
 * @property \Illuminate\Support\Carbon|string $start
 * @property string $end
 * @property-read \App\Models\SessionDescription|null $sessionDescription
 * @property-read \App\Models\SessionGroup $sessionGroup
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Session newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Session newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Session query()
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereSessionDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereSessionGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereStart($value)
 * @mixin \Eloquent
 *
 * @method static \Database\Factories\SessionFactory factory(...$parameters)
 */
class Session extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['session_group_id', 'session_description_id', 'location', 'start', 'end'];

    /**
     * @var string[]
     */
    protected $dates = ['start'];

    /**
     * @return BelongsTo<\App\Models\SessionGroup, \App\Models\Session>
     */
    public function sessionGroup(): BelongsTo
    {
        return $this->belongsTo('App\Models\SessionGroup');
    }

    /**
     * @return HasOne<\App\Models\SessionDescription>
     */
    public function sessionDescription(): HasOne
    {
        return $this->hasOne('App\Models\SessionDescription', 'id', 'session_description_id');
    }
}
