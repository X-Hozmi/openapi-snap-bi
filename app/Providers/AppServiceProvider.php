<?php

namespace App\Providers;

use App\Utils\IPMatcher;
use Carbon\Carbon;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Exception;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route as FacadesRoute;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale(Config::string('app.locale'));

        Scramble::ignoreDefaultRoutes();

        Scramble::configure()
            ->expose(
                ui: '/openapi/v1.0/docs',
                document: '/openapi/v1.0/docs.json',
            )
            ->withDocumentTransformers(function (OpenApi $openApi) {
                /** @var SecurityScheme */
                $securityScheme = SecurityScheme::http('bearer');

                $openApi->secure(
                    $securityScheme
                );
            })
            ->routes(function (Route $route) {
                $prefixes = collect(FacadesRoute::getRoutes()->getRoutes())
                    ->filter(fn ($r) => in_array('api', $r->middleware()))
                    ->map(function ($r) {
                        $segments = explode('/', trim($r->uri(), '/'));

                        return $segments[0];
                    })
                    ->unique()
                    ->filter()
                    ->values()
                    ->all();

                return Str::startsWith($route->uri(), $prefixes);
            });

        Passport::$registersJsonApiRoutes = true;

        Passport::tokensExpireIn(Carbon::now()->addMinutes(15));

        Passport::tokensCan([
            'b2b:inquiry' => 'VA Bill Presentment',
            'b2b:payment' => 'VA Bill Payment Flag',
            'cloud' => 'This is a scope for Cloud Server',
        ]);

        $gates = ['viewApiDocs', 'viewPulse'];

        foreach ($gates as $gate) {
            Gate::define($gate, function ($user = null) {
                /** @var string $clientIp */
                $clientIp = request()->ip();

                $allowedIps = Config::string('app.allowed-ip-addresses', '');

                $isAllowed = IPMatcher::matches($clientIp, $allowedIps);

                return $isAllowed;
            });
        }

        /** @var array<string, int> $rateLimitFor */
        $rateLimitFor = [
            'openapi' => config('app.rate-limit.openapi'),
        ];

        foreach ($rateLimitFor as $key => $value) {
            RateLimiter::for($key, function (Request $request) use ($value) {
                return Limit::perMinute($value)->by($request->ip())->response(function (Request $request, array $headers) {
                    throw new Exception('Too many requests. Please try again later.', 429);
                });
            });
        }

        Livewire::setUpdateRoute(function (array $handle) {
            return FacadesRoute::post('/openapi/v1.0/livewire/update', $handle)->name('custom-livewire.update');
        });

        Livewire::setScriptRoute(function (array $handle) {
            return FacadesRoute::get('/openapi/v1.0/livewire/livewire.js', $handle)->name('custom-livewire.asset');
        });

        if (app()->environment(['production', 'local']) && DB::getDriverName() === 'mysql') {
            DB::statement("SET SESSION sql_mode='STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'");
        }
    }
}
