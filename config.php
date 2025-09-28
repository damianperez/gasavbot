<?php
return [
    // Add you bot's API key and name
   // 'api_key'      => 'DDDD8240097934:AAH-5mUoAw_XzbJoZ5SoV3nGsHBktJPqp3k',
    'bot_username' => 'gasavbot', // Without "@"

    // [Manager Only] Secret key required to access the webhook
    'secret'       => 'super_secret',

    // When using the getUpdates method, this can be commented out
    'webhook'      => [
        'url' => 'https://bots.perezcompany.com.ar/gasavbot/hook.php',
        // Use self-signed certificate
        // 'certificate'     => __DIR__ . '/path/to/your/certificate.crt',
        // Limit maximum number of connections
        // 'max_connections' => 5,
    ],

    // All command related configs go here
    'commands'     => [
        // Define all paths for your custom commands
        // DO NOT PUT THE COMMAND FOLDER THERE. IT WILL NOT WORK. 
        // Copy each needed Commandfile into the CustomCommand folder and uncommend the Line 49 below
        'paths'   => [
             __DIR__ . '/CustomCommands',
        ],
        // Here you can set any command-specific parameters
        'configs' => [
            // - Google geocode/timezone API key for /date command (see DateCommand.php)
            // 'date'    => ['google_api_key' => 'your_google_api_key_here'],
            // - OpenWeatherMap.org API key for /weather command (see WeatherCommand.php)
            // 'weather' => ['owm_api_key' => 'your_owm_api_key_here'],
            // - Payment Provider Token for /payment command (see Payments/PaymentCommand.php)
            // 'payment' => ['payment_provider_token' => 'your_payment_provider_token_here'],
        ],
    ],

    // Define all IDs of admin users
    'admins'       => [
        // 123,
    ],

    // Enter your MySQL database credentials
     'mysql'        => [
         'host'     => '127.0.0.1',
         'user'     => 'bots',
         'password' => 'Bots200',
         'database' => 'bots',
     ],

    // Logging (Debug, Error and Raw Updates)
    // 'logging'  => [
    //     'debug'  => __DIR__ . '/php-telegram-bot-debug.log',
         'error'  => __DIR__ . '/php-telegram-bot-error.log',
    //     'update' => __DIR__ . '/php-telegram-bot-update.log',
    // ],

    // Set custom Upload and Download paths
    'paths'        => [
        'download' => __DIR__ . '/Download',
        'upload'   => __DIR__ . '/Upload',
    ],

    // Requests Limiter (tries to prevent reaching Telegram API limits)
    'limiter'      => [
        'enabled' => true,
    ],
];
