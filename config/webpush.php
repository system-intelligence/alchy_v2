<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Database & Table
    |--------------------------------------------------------------------------
    |
    | These are used by the package's migration and model. If not set,
    | defaults to your app's main database connection and the default
    | push subscriptions table name.
    |
    */

    'database_connection' => env('WEBPUSH_DB_CONNECTION', env('DB_CONNECTION')),
    'table_name' => env('WEBPUSH_TABLE', 'push_subscriptions'),

    /*
    |--------------------------------------------------------------------------
    | VAPID Keys
    |--------------------------------------------------------------------------
    |
    | Generate using: php artisan webpush:vapid
    |
    | WEBPUSH_VAPID_SUBJECT should be a valid URL or a "mailto:" URL.
    |
    */

    'vapid' => [
        'subject' => env('WEBPUSH_VAPID_SUBJECT', 'mailto:admin@example.com'),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | GCM (Deprecated)
    |--------------------------------------------------------------------------
    |
    | Deprecated for modern browsers, kept for backward compatibility.
    |
    */

    'gcm' => [
        'key' => env('GCM_KEY'),
    ],

];
