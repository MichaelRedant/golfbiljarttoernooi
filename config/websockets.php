<?php

return [

    'dashboard' => [
        'port' => env('WEBSOCKETS_PORT', 6001),
    ],

    'apps' => [
        [
            'id' => env('PUSHER_APP_ID'),
            'name' => env('APP_NAME'),
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'path' => env('PUSHER_APP_PATH', '/websockets'),
            'capacity' => null,
            'enable_client_messages' => true,
            'enable_statistics' => true,
        ],
    ],

    'app_provider' => BeyondCode\LaravelWebsockets\Apps\ConfigAppProvider::class,

    'allowed_origins' => [
        //
    ],

    'max_request_size_in_kb' => 250,

    'path' => env('WEBSOCKETS_PATH', '/websockets'),

    'middleware' => [
        'web',
    ],

    'statistics' => [
        'model' => BeyondCode\LaravelWebsockets\Statistics\Models\WebsocketsStatisticsEntry::class,
        'interval_in_seconds' => 60,
        'delete_statistics_older_than_days' => 60,
        'perform_dns_lookup' => true,
    ],

    'ssl' => [
        'local_cert' => env('LARAVEL_WEBSOCKETS_SSL_LOCAL_CERT', null),
        'local_pk' => env('LARAVEL_WEBSOCKETS_SSL_LOCAL_PK', null),
        'passphrase' => env('LARAVEL_WEBSOCKETS_SSL_PASSPHRASE', null),
    ],

    'channel_manager' => \BeyondCode\LaravelWebsockets\WebSockets\Channels\ChannelManagers\ArrayChannelManager::class,
];
