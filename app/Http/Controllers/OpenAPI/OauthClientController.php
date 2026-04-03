<?php

namespace App\Http\Controllers\OpenAPI;

use App\Http\Controllers\Controller;
use App\Http\Requests\OpenAPI\OauthClientRequest;
use App\Models\Oauth\OauthClientMetadata;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\Client;

#[Group('Oauth Client', 'Endpoint for Oauth Client-related data transactions', weight: 51)]
class OauthClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @codeCoverageIgnore
     */
    public function index(): void
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OauthClientRequest $request): JsonResponse
    {
        /**
         * @var array{
         *      name: non-empty-string,
         *      channel_id: int,
         *      partner_id: int,
         *      source_code: int,
         *      kdpp: int,
         *      ip_address: non-empty-string,
         *      public_key_file: non-empty-string,
         *      private_key_file?: non-empty-string,
         *      scope: non-empty-string,
         * } $payload
         */
        $payload = $request->validated();

        Artisan::call('passport:client', [
            '--client' => true,
            '--name' => $payload['name'],
        ]);

        $output = Artisan::output();

        preg_match('/Client ID\s+\.*\s+([a-zA-Z0-9\-]+)/', $output, $idMatches);
        preg_match('/Client Secret\s+\.*\s+([a-zA-Z0-9]+)/', $output, $secretMatches);

        /** @var non-empty-string $clientId */
        $clientId = $idMatches[1];

        /** @var non-empty-string $clientSecret */
        $clientSecret = $secretMatches[1];

        /** @var array<string, mixed> $dataToSave */
        $dataToSave = collect($payload)
            ->except(['name'])
            ->merge([
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ])
            ->toArray();

        OauthClientMetadata::create($dataToSave);

        return response()->json([
            'responseCode' => '2007200',
            'responseMessage' => 'Successful',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @codeCoverageIgnore
     */
    public function update(OauthClientRequest $request, OauthClientMetadata $oauthClientMetadata): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $clientUuid): JsonResponse
    {
        $client = Client::find($clientUuid);

        // @codeCoverageIgnoreStart
        if (! $client) {
            return response()->json([
                'responseCode' => '4047204',
                'responseMessage' => 'Client not found',
            ], 404);
        }
        // @codeCoverageIgnoreEnd

        $client->delete();

        return response()->json([
            'responseCode' => '2007200',
            'responseMessage' => 'Client deleted successfully',
        ]);
    }
}
