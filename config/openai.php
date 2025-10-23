<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key and Organization
    |--------------------------------------------------------------------------
    |
    | Here you may specify your OpenAI API Key and organization. This will be
    | used to authenticate with the OpenAI API - you can find your API key
    | and organization on your OpenAI dashboard, at https://openai.com.
    */

    'api_key' => env('OPENAI_API_KEY'),
    'organization' => env('OPENAI_ORGANIZATION'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Project
    |--------------------------------------------------------------------------
    |
    | Here you may specify your OpenAI API project. This is used optionally in
    | situations where you are using a legacy user API key and need association
    | with a project. This is not required for the newer API keys.
    */
    'project' => env('OPENAI_PROJECT'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Base URL
    |--------------------------------------------------------------------------
    |
    | Here you may specify your OpenAI API base URL used to make requests. This
    | is needed if using a custom API endpoint. Defaults to: api.openai.com/v1
    */
    'base_uri' => env('OPENAI_BASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout may be used to specify the maximum number of seconds to wait
    | for a response. By default, the client will time out after 30 seconds.
    */

    'request_timeout' => env('OPENAI_REQUEST_TIMEOUT', 30),
    'connect_timeout' => env('OPENAI_CONNECT_TIMEOUT', 10),
    'max_retries' => env('OPENAI_MAX_RETRIES', 2),
    'retry_delay_ms' => env('OPENAI_RETRY_DELAY_MS', 1000),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Realtime Defaults
    |--------------------------------------------------------------------------
    |
    | These options define the default model and voice that will be used when
    | creating short-lived realtime sessions for voice conversations.
    */

    'realtime_model' => env('OPENAI_REALTIME_MODEL', 'gpt-realtime-mini-2025-10-06'),
    'realtime_voice' => env('OPENAI_REALTIME_VOICE', 'alloy'),

    'text_chat_model' => env('OPENAI_TEXT_CHAT_MODEL', 'gpt-4.1'),
    'text_chat_temperature' => env('OPENAI_TEXT_CHAT_TEMPERATURE', 0.7),
];
