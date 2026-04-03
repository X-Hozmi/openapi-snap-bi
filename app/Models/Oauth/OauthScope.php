<?php

namespace App\Models\Oauth;

use Database\Factories\Oauth\OauthScopeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static \Database\Factories\Oauth\OauthScopeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthScope newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthScope newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthScope onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthScope query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthScope whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthScope whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthScope whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthScope whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthScope whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthScope whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthScope withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthScope withoutTrashed()
 *
 * @mixin \Eloquent
 */
class OauthScope extends Model
{
    /** @use HasFactory<OauthScopeFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): OauthScopeFactory
    {
        return OauthScopeFactory::new();
    }
}
