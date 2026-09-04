<?php

namespace App\Providers;

use App\Contracts\CareerAdviserClient;
use App\Contracts\CareerAiClient;
use App\Services\AI\HttpCareerAiClient;
use App\Services\AI\MockCareerAdviserClient;
use App\Services\AI\MockCareerAiClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            CareerAiClient::class,
            function () {
                return match (config('career-ai.driver')) {
                    'mock' => new MockCareerAiClient(),
                    'http' => new HttpCareerAiClient(),

                    default => throw new \RuntimeException(
                        'Unsupported Career AI driver: '
                        . config('career-ai.driver')
                    ),
                };
            }
        );

        /*
         * Career Adviser uses the mock client until the
         * external Adviser API contract is available.
         */
        $this->app->bind(
            CareerAdviserClient::class,
            MockCareerAdviserClient::class
        );
    }

    public function boot(): void
    {
        //
    }
}