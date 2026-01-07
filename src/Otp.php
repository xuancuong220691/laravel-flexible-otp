<?php

namespace CuongNX\LaravelFlexibleOtp;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Cuongnx\LaravelFlexibleOtp\Events\OtpGenerated;

class Otp
{
    protected $model;

    public function __construct()
    {
        $this->model = app(config('otp.model'));
    }

    protected function generateRandomOtp($type, $length)
    {
        return match ($type) {
            'numeric' => substr(str_shuffle('0123456789'), 0, $length),
            'alpha_numeric' => Str::random($length),
            'alpha' => substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length),
            default => throw new \Exception('Invalid OTP type'),
        };
    }

    protected function deleteOldOtps($identifier)
    {
        $this->model::where('identifier', $identifier)->delete();
    }

    /**
     * Generate a random OTP for the given user
     *

     * @param string $originalIdentifier the user identifier
     * @param string|null $purpose the purpose of the OTP
     * @param string|null $type the type of the OTP
     * @param int|null $length the length of the OTP
     * @param int|null $validity the validity of the OTP
     * @param int|null $resendCooldown the cooldown time after which the OTP can be resent
     * @param string|null $send whether to send the OTP or not
     * @param string|null $provider the provider to use for sending sending OTP
     *
     * @return object an object with the status, message
     */
    public function generate($originalIdentifier, $purpose = null, $type = null, $length = null, $validity = null, $resendCooldown = null, $send = false, $provider = null)
    {
        $maxAttempts = 0;
        $decayMinutes = 0;

        if ($purpose && isset(config('otp.purposes')[$purpose])) {
            $purposeConfig = config('otp.purposes')[$purpose];
            $type ??= $purposeConfig['type'] ?? config('otp.type');
            $length ??= $purposeConfig['length'] ?? config('otp.length');
            $validity ??= $purposeConfig['validity'] ?? config('otp.validity');
            $resendCooldown ??= $purposeConfig['resend_cooldown'] ?? config('otp.resend_cooldown');
            $maxAttempts = $purposeConfig['max_attempts'] ?? 0;
            $decayMinutes = $purposeConfig['decay_minutes'] ?? 0;
        } else {
            $type ??= config('otp.type');
            $length ??= config('otp.length');
            $validity ??= config('otp.validity');
            $resendCooldown ??= config('otp.resend_cooldown');
            $maxAttempts = config('otp.max_attempts') ?? 0;
            $decayMinutes = config('otp.decay_minutes') ?? 0;
        }

        $identifier = $purpose ? $originalIdentifier . ':' . $purpose : $originalIdentifier;

        $cacheKey = "otp:last_sent:{$identifier}";
        $lastSent = Cache::get($cacheKey);

        $resendAfterSeconds = $resendCooldown * 60;

        if ($lastSent) {
            $secondsSinceLastSent = $lastSent->diffInSeconds(now());
            $remainingSeconds = (int) ceil($resendAfterSeconds - $secondsSinceLastSent);

            if ($remainingSeconds > 0) {
                return (object) [
                    'status' => false,
                    'code' => 'resend_cooldown',
                    'message' => 'Please wait before resending',
                    'resend_after' => $remainingSeconds,
                ];
            }
        }

        if ($maxAttempts > 0 && $decayMinutes > 0) {
            $rateLimitKey = "otp:rate_limit:{$identifier}";
            $expireKey = "otp:rate_limit_expire:{$identifier}";

            $attempts = Cache::get($rateLimitKey, 0);

            if ($attempts >= $maxAttempts) {
                $expireAt = Cache::get($expireKey);

                if ($expireAt) {
                    $remainingSeconds = max(0, $expireAt->diffInSeconds(now()));
                } else {
                    $remainingSeconds = $decayMinutes * 60;
                }

                return (object) [
                    'status' => false,
                    'code' => 'rate_limit_exceeded',
                    'message' => 'Maximum OTP generation attempts exceeded. Please try again later.',
                    'resend_after' => (int) $remainingSeconds,
                    'rate_limit_reset_in' => (int) ceil($remainingSeconds / 60) . ' minute(s)',
                ];
            }
        }

        $this->deleteOldOtps($identifier);

        $plainOtp = $this->generateRandomOtp($type, $length);

        $hashedOtp = Hash::make($plainOtp);

        $this->model::create([
            'identifier' => $identifier,
            'token' => $hashedOtp,
            'expires_at' => now()->addMinutes($validity),
        ]);

        Cache::put($cacheKey, now(), $resendAfterSeconds);

        if ($maxAttempts > 0 && $decayMinutes > 0) {
            $rateLimitKey = "otp:rate_limit:{$identifier}";
            $expireKey = "otp:rate_limit_expire:{$identifier}";

            $currentAttempts = Cache::increment($rateLimitKey);

            if ($currentAttempts === 1) {
                Cache::put($rateLimitKey, 1, $decayMinutes * 60);
                Cache::put($expireKey, now()->addMinutes($decayMinutes), $decayMinutes * 60);
            }
        }

        $sendProvider = $provider ?? config('otp.send_provider');

        if ($send) {
            Event::dispatch(new OtpGenerated($originalIdentifier, $plainOtp, $sendProvider));
            $response = (object) [
                'status' => true,
                'message' => 'OTP generated and sent',
                'resend_after' => $resendAfterSeconds,
                'validity' => $validity,
            ];
        } else {
            $response = (object) [
                'status' => true,
                'message' => 'OTP generated',
                'token' => $plainOtp,
                'resend_after' => $resendAfterSeconds,
                'validity' => $validity,
            ];
        }

        if (!config('otp.debug') && isset($response->token)) {
            unset($response->token);
        }

        return $response;
    }

    /**
     * Validate an OTP for the given identifier and token
     *
     * @param string $originalIdentifier
     * @param string $token
     * @param string|null $purpose
     * @return object
     */

    public function validate($originalIdentifier, $token, $purpose = null)
    {
        $identifier = $purpose ? $originalIdentifier . ':' . $purpose : $originalIdentifier;

        $otpRecord = $this->model::where('identifier', $identifier)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otpRecord) {
            return (object) ['status' => false, 'message' => 'OTP does not exist or expired'];
        }

        if (!Hash::check($token, $otpRecord->token)) {
            return (object) ['status' => false, 'message' => 'OTP is not valid'];
        }

        $otpRecord->delete();

        return (object) ['status' => true, 'message' => 'OTP is valid'];
    }

    /**
     * Validate an OTP for the given identifier and token
     *
     * @param string $originalIdentifier
     * @param string $token
     * @param string|null $purpose
     * @return bool
     */
    public function isValid($originalIdentifier, $token, $purpose = null)
    {
        $identifier = $purpose ? $originalIdentifier . ':' . $purpose : $originalIdentifier;

        $otpRecord = $this->model::where('identifier', $identifier)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otpRecord) {
            return false;
        }

        return Hash::check($token, $otpRecord->token);
    }

    /**
     * Delete expired OTP records.
     *
     * @return void
     */
    public function clean()
    {
        $this->model::where('expires_at', '<', now())->delete();
    }
}
