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
    | Inference Transport
    |--------------------------------------------------------------------------
    |
    | Which service actually runs the model. The driver above decides
    | what we ask for — the prompts, the JSON schemas, the BIICF
    | wording — and this decides who answers. They are separate so a
    | provider can be changed without touching anything that affects
    | what a student is told.
    |
    | "groq"       talks to Groq directly.
    | "openrouter" routes to whichever upstream is cheapest.
    |
    */

    'transport' => env(
        'CAREER_AI_TRANSPORT',
        'groq'
    ),

    /*
    |--------------------------------------------------------------------------
    | Career Adviser Temperature
    |--------------------------------------------------------------------------
    |
    | How freely the adviser may phrase an answer. This is a product
    | decision rather than a provider setting — competency names and
    | proficiency levels are defined BIICF terms and must come back
    | unparaphrased — so it applies whichever transport is in use.
    |
    */

    'adviser_temperature' => env(
        'CAREER_ADVISER_TEMPERATURE',
        env('GROQ_ADVISER_TEMPERATURE', 0.5)
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

        /*
         * How freely the model may phrase an answer. Low values
         * keep it close to the supplied competency names and
         * levels, which are defined terms in BIICF and should
         * not be paraphrased.
         */
        'temperature' => env(
            'GROQ_TEMPERATURE',
            null
        ),

        'reasoning_effort' => env(
            'GROQ_REASONING_EFFORT',
            'medium'
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenRouter
    |--------------------------------------------------------------------------
    |
    | OpenRouter fronts many providers behind one OpenAI-compatible
    | API, so the same GPT-OSS model can be reached without holding
    | an account with each one.
    |
    */

    'openrouter' => [
        'base_url' => env(
            'OPENROUTER_BASE_URL',
            'https://openrouter.ai/api/v1'
        ),

        'api_key' => env(
            'OPENROUTER_API_KEY'
        ),

        'endpoint' => env(
            'OPENROUTER_CHAT_ENDPOINT',
            '/chat/completions'
        ),

        /*
         * The same GPT-OSS model the project has always used. Kept
         * deliberately: the prompts and JSON schemas are tuned to
         * this family, so changing provider is cheap and changing
         * model is not.
         */
        'model' => env(
            'OPENROUTER_MODEL',
            'openai/gpt-oss-20b'
        ),

        'temperature' => env(
            'OPENROUTER_TEMPERATURE',
            null
        ),

        /*
         * Matches what Groq has always been told. Left unset, a
         * reasoning model can spend its whole output budget
         * thinking about a long prompt and return nothing at all.
         * Sent as OpenRouter's own "reasoning" parameter, so an
         * upstream that does not reason ignores it rather than
         * rejecting the request. "low", "medium" or "high"; empty
         * hands the decision back to the upstream.
         */
        'reasoning_effort' => env(
            'OPENROUTER_REASONING_EFFORT',
            'medium'
        ),

        /*
         * Ceiling on the reply, leaving room for an answer after
         * the reasoning. Zero omits it and lets the upstream
         * decide. The adviser's replies are a few hundred tokens,
         * so this is headroom rather than a target.
         */
        'max_tokens' => (int) env(
            'OPENROUTER_MAX_TOKENS',
            4000
        ),

        /*
         * Restricts routing to upstreams that honour every
         * parameter sent. Both career clients throw when a reply
         * does not match their schema, so an upstream that quietly
         * ignores response_format fails rather than degrades.
         */
        'require_parameters' => (bool) env(
            'OPENROUTER_REQUIRE_PARAMETERS',
            true
        ),

        /*
         * Prices for one model vary several-fold between upstreams.
         * Set to an empty string to let OpenRouter balance instead.
         */
        'provider_sort' => env(
            'OPENROUTER_PROVIDER_SORT',
            'price'
        ),

        /*
         * Comma-separated upstreams to route around. For ones that
         * answer badly rather than not at all — returning GPT-OSS's
         * thinking while leaving the message empty, say — which
         * looks like a normal completion and so is never retried.
         * The empty-content error names the upstream that failed,
         * so add it here.
         */
        'provider_ignore' => env(
            'OPENROUTER_PROVIDER_IGNORE',
            ''
        ),

        /*
         * Comma-separated upstreams allowed to serve this, in order
         * of preference; no others are used. Pinning a known-good
         * one trades a little money for not having to re-verify
         * behaviour whenever routing moves. Groq is an upstream
         * here, so this is how the project keeps Groq's speed and
         * behaviour while billing through OpenRouter. Empty routes
         * across everything.
         */
        'provider_only' => env(
            'OPENROUTER_PROVIDER_ONLY',
            ''
        ),

        /*
         * Optional. OpenRouter uses these to attribute requests.
         */
        'site_url' => env(
            'OPENROUTER_SITE_URL',
            env('APP_URL')
        ),

        'site_name' => env(
            'OPENROUTER_SITE_NAME',
            'CareerPath BN'
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Direct Providers
    |--------------------------------------------------------------------------
    |
    | Hosts running the same model behind OpenAI's own API shape, so
    | OpenAiCompatibleClient serves all of them and a new one is a
    | block like these rather than a new class. Set the transport to
    | the block's name to use it.
    |
    | Copy the model string from the provider's own model page: base
    | URLs are predictable, model names are not.
    |
    */

    'deepinfra' => [
        'base_url' => env(
            'DEEPINFRA_BASE_URL',
            'https://api.deepinfra.com/v1/openai'
        ),

        'api_key' => env(
            'DEEPINFRA_API_KEY'
        ),

        'endpoint' => env(
            'DEEPINFRA_CHAT_ENDPOINT',
            '/chat/completions'
        ),

        'model' => env(
            'DEEPINFRA_MODEL',
            'openai/gpt-oss-20b'
        ),

        'temperature' => env(
            'DEEPINFRA_TEMPERATURE',
            null
        ),
    ],

    'together' => [
        'base_url' => env(
            'TOGETHER_BASE_URL',
            'https://api.together.xyz/v1'
        ),

        'api_key' => env(
            'TOGETHER_API_KEY'
        ),

        'endpoint' => env(
            'TOGETHER_CHAT_ENDPOINT',
            '/chat/completions'
        ),

        'model' => env(
            'TOGETHER_MODEL',
            'openai/gpt-oss-20b'
        ),

        'temperature' => env(
            'TOGETHER_TEMPERATURE',
            null
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
