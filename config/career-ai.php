<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Career AI Driver
    |--------------------------------------------------------------------------
    |
    | "mock" is used while the actual AI API is unavailable.
    | This can later be changed to "http" when the AI service is ready.
    |
    */

    'driver' => env('CAREER_AI_DRIVER', 'mock'),

    /*
    |--------------------------------------------------------------------------
    | AI API Connection
    |--------------------------------------------------------------------------
    */

    'base_url' => env('CAREER_AI_BASE_URL'),

    'api_key' => env('CAREER_AI_API_KEY'),

    'endpoint' => env(
        'CAREER_AI_ENDPOINT',
        '/api/v1/recommendations'
    ),

    'timeout' => (int) env('CAREER_AI_TIMEOUT', 30),

];