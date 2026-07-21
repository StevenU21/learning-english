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
    |
    */

    'api_key' => env('OPENAI_API_KEY'),
    'organization' => env('OPENAI_ORGANIZATION'),
    'base_uri' => env('OPENAI_BASE_URI', 'https://api.openai.com/v1'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeouts & Retries
    |--------------------------------------------------------------------------
    */

    'request_timeout' => env('OPENAI_REQUEST_TIMEOUT', 30),
    'connect_timeout' => env('OPENAI_CONNECT_TIMEOUT', 10),
    'max_retries' => env('OPENAI_MAX_RETRIES', 2),
    'retry_delay_ms' => env('OPENAI_RETRY_DELAY_MS', 1000),

    /*
    |--------------------------------------------------------------------------
    | Text Chat Service Settings
    |--------------------------------------------------------------------------
    */

    'text_chat_model' => env('OPENAI_TEXT_CHAT_MODEL', 'gpt-4o-mini'),
    'text_chat_temperature' => env('OPENAI_TEXT_CHAT_TEMPERATURE', 0.7),

    /*
    |--------------------------------------------------------------------------
    | Realtime Service Settings
    |--------------------------------------------------------------------------
    */

    'realtime_model' => env('OPENAI_REALTIME_MODEL', 'gpt-realtime-mini-2025-10-06'),
    'realtime_voice' => env('OPENAI_REALTIME_VOICE', 'alloy'),
    'realtime_session_duration' => env('OPENAI_REALTIME_SESSION_DURATION', 120),

];
