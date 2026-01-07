<?php

namespace CuongNX\LaravelFlexibleOtp\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \CuongNX\LaravelFlexibleOtp\Otp
 */
class Otp extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \CuongNX\LaravelFlexibleOtp\Otp::class;
    }
}
