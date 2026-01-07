<?php

namespace CuongNX\LaravelFlexibleOtp\Console\Commands;

use Illuminate\Console\Command;
use CuongNX\LaravelFlexibleOtp\Otp;

class CleanOtpCommand extends Command
{
    protected $signature = 'otp:clean';
    protected $description = 'Clean expired OTPs';

    public function handle()
    {
        (new Otp)->clean();
        $this->info('Expired OTPs cleaned successfully.');
    }
}