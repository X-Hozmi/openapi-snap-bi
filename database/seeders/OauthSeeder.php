<?php

namespace Database\Seeders;

use App\Models\Oauth\OauthClientMetadata;
use Illuminate\Database\Seeder;
use Laravel\Passport\Client;

class OauthSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $client = Client::find(config('passport.oauth.openapi.client-key'));

        OauthClientMetadata::factory()->create([
            'client_id' => $client?->id,
            'client_secret' => config('passport.oauth.openapi.client-secret'),
            'channel_id' => config('passport.oauth.openapi.channel-id'),
            'partner_id' => config('passport.oauth.openapi.partner-id'),
            'ip_address' => config('passport.oauth.openapi.ip-address'),
            'public_key_file' => config('passport.oauth.openapi.public-key-file'),
            'private_key_file' => config('passport.oauth.openapi.private-key-file'),
            'scope' => config('passport.oauth.openapi.scope'),
        ]);
    }
}
