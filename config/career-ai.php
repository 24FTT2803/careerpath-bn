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
    |
    | These values configure communication with the external Career AI API.
    | The endpoint and authentication requirements are provisional until the
    | final API contract is provided.
    |
    */

    'base_url' => env('CAREER_AI_BASE_URL'),

    'api_key' => env('CAREER_AI_API_KEY'),

    'endpoint' => env(
        'CAREER_AI_ENDPOINT',
        '/api/v1/recommendations'
    ),

    /*
    |--------------------------------------------------------------------------
    | Request Timeouts
    |--------------------------------------------------------------------------
    |
    | timeout controls the maximum duration of the full HTTP request.
    | connect_timeout controls how long Laravel waits to establish the
    | connection to the Career AI service.
    |
    */

    'timeout' => (int) env(
        'CAREER_AI_TIMEOUT',
        30
    ),

    'connect_timeout' => (int) env(
        'CAREER_AI_CONNECT_TIMEOUT',
        10
    ),

];