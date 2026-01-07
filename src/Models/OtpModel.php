<?php

namespace CuongNX\LaravelFlexibleOtp\Models;

use Illuminate\Database\Eloquent\Model;

class OtpModel extends Model
{
    protected $table = 'one_time_passwords';
    protected $connection;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('otp.connection');
    }

    protected $fillable = ['identifier', 'token', 'expires_at'];
}