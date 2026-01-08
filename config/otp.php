<?php

return [
    'model' => \CuongNX\LaravelFlexibleOtp\Models\OtpModel::class,
    'validity' => 10, // default validity time in minutes
    'resend_cooldown' => 2, // default resend cooldown time in minutes
    'length' => 6,
    'type' => 'numeric', // options: numeric, alpha, alpha_numeric
    'send_provider' => 'none', // Default provider: none, mail, zalo_zns, speedsms,...
    'connection' => 'mysql', // // Storage: mongodb, mysql, sqlite, pgsql...
    'debug' => env('APP_DEBUG', false), // return plain OTP in response for testing purpose
    'purposes' => [
        'login' => [
            'type' => 'numeric',
            'length' => 4,
            'validity' => 5,
            'resend_cooldown' => 1,
            'send_provider' => 'none',
        ],
        'verify_phone' => [
            'type' => 'alpha_numeric',
            'length' => 6,
            'validity' => 15,
            'resend_cooldown' => 3,
            'send_provider' => 'none',
        ],
        'reset_password' => [
            'type' => 'alpha',
            'length' => 8,
            'validity' => 10,
            'resend_cooldown' => 2,
            'send_provider' => 'none',
        ],
    ],
];
