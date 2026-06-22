<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Stripe (CU7 — Pago de cuotas y multas con tarjeta)
    |--------------------------------------------------------------------------
    | Claves de prueba: https://dashboard.stripe.com/test/apikeys
    | El webhook_secret se obtiene con `stripe listen` (local) o desde el
    | dashboard de Stripe (producción/nube).
    */
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'currency' => env('STRIPE_CURRENCY', 'bob'),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI (CU12 — Reportes por voz/texto)
    |--------------------------------------------------------------------------
    | Whisper transcribe el audio y el modelo de chat interpreta la instrucción
    | en lenguaje natural para generar la especificación del reporte.
    | Configura OPENAI_API_KEY en tu archivo .env.
    */
    'openai' => [
        'api_key'       => env('OPENAI_API_KEY'),
        'model'         => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'whisper_model' => env('OPENAI_WHISPER_MODEL', 'whisper-1'),
        'base_url'      => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'timeout'       => env('OPENAI_TIMEOUT', 60),
    ],

];
