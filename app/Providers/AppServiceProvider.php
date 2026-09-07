<?php

namespace App\Providers;

use App\Contracts\CareerAdviserClient;
use App\Contracts\CareerAiClient;
use App\Services\AI\GroqCareerAdviserClient;
use App\Services\AI\GroqCareerAiClient;
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
            function ($app) {
                return match (config('career-ai.driver')) {
                    'mock' => $app->make(
                        MockCareerAiClient::class
                    ),

                    'http' => $app->make(
                        HttpCareerAiClient::class
                    ),

                    'groq' => $app->make(
                        GroqCareerAiClient::class
                    ),

                    default => throw new \RuntimeException(
                        'Unsupported Career AI driver: '
                        . config('career-ai.driver')
                    ),
                };
            }
        );

        $this->app->bind(
            CareerAdviserClient::class,
            function ($app) {
                return match (config('career-ai.adviser_driver')) {
                    'mock' => $app->make(
                        MockCareerAdviserClient::class
                    ),

                    'groq' => $app->make(
                        GroqCareerAdviserClient::class
                    ),

                    default => throw new \RuntimeException(
                        'Unsupported Career Adviser driver: '
                        . config('career-ai.adviser_driver')
                    ),
                };
            }
        );
    }

    public function boot(): void
    {
        //
    }
}