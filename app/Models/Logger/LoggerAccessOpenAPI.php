<?php

namespace App\Models\Logger;

use Database\Factories\Logger\LoggerAccessOpenAPIFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $method
 * @property string $url
 * @property string $ip_address
 * @property array<array-key, mixed> $request_headers
 * @property array<array-key, mixed> $response_headers
 * @property array<array-key, mixed>|null $request_contents
 * @property array<array-key, mixed>|null $response_contents
 * @property int $response_code
 * @property float $processing_time in milliseconds
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Database\Factories\Logger\LoggerAccessOpenAPIFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoggerAccessOpenAPI newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoggerAccessOpenAPI newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoggerAccessOpenAPI query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoggerAccessOpenAPI whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoggerAccessOpenAPI whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoggerAccessOpenAPI whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoggerAccessOpenAPI whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoggerAccessOpenAPI whereProcessingTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoggerAccessOpenAPI whereRequestContents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoggerAccessOpenAPI whereRequestHeaders($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoggerAccessOpenAPI whereResponseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoggerAccessOpenAPI whereResponseContents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoggerAccessOpenAPI whereResponseHeaders($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoggerAccessOpenAPI whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoggerAccessOpenAPI whereUrl($value)
 *
 * @mixin \Eloquent
 */
class LoggerAccessOpenAPI extends Model
{
    /** @use HasFactory<LoggerAccessOpenAPIFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'logger_access_openapi';

    protected $guarded = ['id'];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): LoggerAccessOpenAPIFactory
    {
        return LoggerAccessOpenAPIFactory::new();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'request_headers' => 'array',
            'response_headers' => 'array',
            'request_contents' => 'array',
            'response_contents' => 'array',
        ];
    }
}
