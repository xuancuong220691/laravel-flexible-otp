<?php

namespace CuongNX\LaravelFlexibleOtp\Events;

use Illuminate\Foundation\Events\Dispatchable;

class OtpGenerated
{
    use Dispatchable;

    public $sendTo;
    public $plainOtp;
    public $provider;

    public function __construct($sendTo, $plainOtp, $provider)
    {
        $this->sendTo = $sendTo;
        $this->plainOtp = $plainOtp;
        $this->provider = $provider;
    }
}
