<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Termii SMS Configuration
    |--------------------------------------------------------------------------
    |
    | API credentials for the Termii SMS gateway (https://developers.termii.com).
    | The channel is a no-op when api_key is empty, so the app stays functional
    | in local environments without SMS credentials.
    |
    */

    'api_key' => env('TERMII_API_KEY', ''),

    'sender_id' => env('TERMII_SENDER_ID', 'NAPTIN-COOP'),

    'base_url' => env('TERMII_BASE_URL', 'https://api.ng.termii.com/api'),

    'channel' => env('TERMII_CHANNEL', 'generic'),

];
