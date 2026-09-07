<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Career AI Driver
    |--------------------------------------------------------------------------
    |
    | "mock" keeps the local mock implementation.
    | "groq" will use Groq with the configured GPT-OSS model.
    | "http" preserves the earlier generic HTTP integration.
    |
    */

    'driver' => env(
        'CAREER_AI_DRIVER',
        'mock'
    ),

    /*
    |--------------------------------------------------------------------------
    | Career Adviser Driver
    |--------------------------------------------------------------------------
    |
    | Allows the Career Adviser to use Groq independently while the
    | Career Recommendation integration is still being completed.
    |
    */

    'adviser_driver' => env(
        'CAREER_ADVISER_DRIVER',
        'mock'
    ),

    /*
    |--------------------------------------------------------------------------
    | Legacy Generic HTTP Client
    |--------------------------------------------------------------------------
    |
    | These settings are retained for the existing HttpCareerAiClient.
    |
    */

    'base_url' => env(
        'CAREER_AI_BASE_URL'
    ),

    'api_key' => env(
        'CAREER_AI_API_KEY'
    ),

    'endpoint' => env(
        'CAREER_AI_ENDPOINT',
        '/api/v1/recommendations'
    ),

    /*
    |--------------------------------------------------------------------------
    | Groq
    |--------------------------------------------------------------------------
    |
    | Groq provides the inference API used to run GPT-OSS.
    |
    */

    'groq' => [
        'base_url' => env(
            'GROQ_BASE_URL',
            'https://api.groq.com/openai/v1'
        ),

        'api_key' => env(
            'GROQ_API_KEY'
        ),

        'endpoint' => env(
            'GROQ_CHAT_ENDPOINT',
            '/chat/completions'
        ),

        'model' => env(
            'GROQ_MODEL',
            'openai/gpt-oss-20b'
        ),

        'reasoning_effort' => env(
            'GROQ_REASONING_EFFORT',
            'medium'
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Timeouts
    |--------------------------------------------------------------------------
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