<?php

namespace App\Models\Oauth;

use Database\Factories\Oauth\OauthClientMetadataFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Laravel\Passport\Client;

/**
 * @property string $name
 * @property string $client_id
 * @property string $client_secret
 * @property int $channel_id
 * @property int $partner_id
 * @property string|null $source_code
 * @property string|null $kdpp
 * @property string|null $ip_address
 * @property string $public_key_file
 * @property string|null $private_key_file
 * @property string $scope
 * @property int $rupiah_admin
 * @property string|null $ip_address
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Client $client
 *
 * @method static \Database\Factories\Oauth\OauthClientMetadataFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthClientMetadata newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthClientMetadata newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthClientMetadata query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthClientMetadata whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthClientMetadata whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthClientMetadata whereClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthClientMetadata whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthClientMetadata whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthClientMetadata whereKdpp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthClientMetadata wherePartnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthClientMetadata wherePublicKeyFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthClientMetadata whereScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthClientMetadata whereSourceCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthClientMetadata whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthClientMetadata wherePrivateKeyFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OauthClientMetadata whereRupiahAdmin($value)
 *
 * @mixin \Eloquent
 */
class OauthClientMetadata extends Model
{
    /** @use HasFactory<OauthClientMetadataFactory> */
    use HasFactory;

    protected $guarded = ['uuid'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oauth_client_metadatas';

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): OauthClientMetadataFactory
    {
        return OauthClientMetadataFactory::new();
    }

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'client_id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
