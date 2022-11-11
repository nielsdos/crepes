<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SessionDescription
 *
 * @property int $id
 * @property string $description
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SessionDescription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SessionDescription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SessionDescription query()
 * @method static \Illuminate\Database\Eloquent\Builder|SessionDescription whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SessionDescription whereId($value)
 * @mixin \Eloquent
 *
 * @method static \Database\Factories\SessionDescriptionFactory factory(...$parameters)
 */
class SessionDescription extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['description'];
}
