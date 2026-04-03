<?php

namespace Database\Factories\Oauth;

use App\Models\Oauth\OauthClientMetadata;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<OauthClientMetadata>
 */
class OauthClientMetadataFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = OauthClientMetadata::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => fake()->uuid(),
            'client_secret' => fake()->md5(),
            'channel_id' => fake()->randomNumber(5, true),
            'partner_id' => fake()->randomNumber(5, true),
            'ip_address' => fake()->ipv4(),
            'public_key_file' => fake()->word(),
            'private_key_file' => fake()->word(),
            'scope' => fake()->word(),
        ];
    }
}
