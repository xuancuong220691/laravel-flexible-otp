<?php

namespace CuongNX\LaravelFlexibleOtp;

use Illuminate\Support\ServiceProvider;

class OtpServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/otp.php' => config_path('otp.php'),
        ], 'otp-config');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \CuongNX\LaravelFlexibleOtp\Console\Commands\CleanOtpCommand::class,
                \CuongNX\LaravelFlexibleOtp\Console\Commands\MakeOtpListenerCommand::class,
                \CuongNX\LaravelFlexibleOtp\Console\Commands\MakeOtpModelCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/otp.php', 'otp');
    }
}