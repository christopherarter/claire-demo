<?php

namespace App\Services\PartnerService;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\ServiceProvider;

class PartnerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->bind(Factory::class, function () {
            return (new Factory)->stubUrl(config('partner-service.base_url').'*', function (Request $request) {
                return app(PartnerRequestFactory::class)->handleStubRequest($request);
            });
        });
    }
}
