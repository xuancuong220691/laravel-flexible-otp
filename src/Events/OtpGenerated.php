<?php

namespace CuongNX\LaravelFlexibleOtp\Events;

use Illuminate\Foundation\Events\Dispatchable;

class OtpGenerated
{
    use Dispatchable;

    public $identifier;
    public $plainOtp;
    public $provider;

    public function __construct($identifier, $plainOtp, $provider)
    {
        $this->identifier = $identifier;
        $this->plainOtp = $plainOtp;
        $this->provider = $provider;
    }
}