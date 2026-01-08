# cuongnx/laravel-flexible-otp

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cuongnx/laravel-flexible-otp.svg?style=flat-square)](https://packagist.org/packages/cuongnx/laravel-flexible-otp)
[![Total Downloads](https://img.shields.io/packagist/dt/cuongnx/laravel-flexible-otp.svg?style=flat-square)](https://packagist.org/packages/cuongnx/laravel-flexible-otp)
[![License](https://img.shields.io/packagist/l/cuongnx/laravel-flexible-otp.svg?style=flat-square)](https://github.com/cuongnx/laravel-flexible-otp/blob/main/LICENSE)

A powerful and highly flexible One-Time Password (OTP) package for Laravel applications.

---

## ✨ Key Features

* **Multi-purpose configuration** – Define separate OTP settings for different use cases (login, phone verification, password reset, etc.).
* **Multi-storage support** – Works seamlessly with MySQL or MongoDB.
* **Per-purpose cooldown & rate limiting** – Prevent abuse with fine-grained control.
* **Optional automatic sending** – Built-in support for Zalo ZNS, SpeedSMS, Mail, or any custom provider.
* **Modern PHP 8+ named arguments** – Clean, readable, and type-safe API.

---

## 📦 Requirements

* PHP ^8.2
* Laravel ^11.0 or ^12.0

---

## 🚀 Installation

```bash
composer require cuongnx/laravel-flexible-otp
```

The package auto-registers its service provider and facade.

---

## ⚙️ Configuration

Publish the configuration file (recommended):

```bash
php artisan vendor:publish --tag=otp-config
```

This will create:

```
config/otp.php
```

---

## 🧩 Create the OTP Model

The package supports both **MySQL** and **MongoDB**.

Generate the appropriate model:

```bash
php artisan otp:make-model
```

* If `connection` is set to `mongodb` → the model extends `MongoDB\\Laravel\\Eloquent\\Model`
* Otherwise → the model extends `Illuminate\\Database\\Eloquent\\Model`

After generation, update the config:

```php
'model' => App\\Models\\OtpRecord::class, // or the name you chose
```

---

## 🗄 Migration

A migration is included and automatically published. Run:

```bash
php artisan migrate
```

This creates the `one_time_passwords` table or collection.

---

## 🛠 Usage

```php
use Cuongnx\\LaravelFlexibleOtp\\Facades\\Otp;

// Generate OTP normal (returns plain token, does not send)
$response = Otp::generate('+84123456789');

// Generate OTP with purpose (returns plain token, does not send)
$response = Otp::generate('+84123456789', purpose: 'verify_phone');

// Generate and automatically send the OTP
$response = Otp::generate(
    '+84123456789',
    purpose: 'verify_phone',
    send: true,
    provider: 'zalo_zns' // or 'speedsms', 'mail'
);

// Validate OTP
$result = Otp::validate('+84123456789', '123456', purpose: 'verify_phone');

if ($result->status) {
    // OTP is valid
}

// Check validity without consuming
$isValid = Otp::isValid('+84123456789', '123456', purpose: 'verify_phone');
```

---

## 🧾 Example Configuration (`config/otp.php`)

```php
return [
    'validity' => 10,                    // minutes
    'resend_cooldown' => 2,              // minutes
    'length' => 6,
    'type' => 'numeric',
    'send_provider' => 'none',           // none | mail | zalo_zns | speedsms
    'connection' => 'mongodb',           // mysql | mongodb
    'model' => App\\Models\\OtpRecord::class,

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
        ],
        // Add more purposes as needed
    ],
];
```

---

## 📩 Automatic OTP Sending

When `send: true` is passed, the package dispatches an `OtpGenerated` event.

Create a listener to handle sending:

```bash
php artisan make:listener SendOtpListener --event="CuongNX\LaravelFlexibleOtp\Events\OtpGenerated"
```

Then implement your sending logic inside the handle method.

#### Quick Start: Generate Ready-to-Use Listener

The package provides a convenient command to create a complete listener with pre-built support for popular providers:

```bash
php artisan otp:make-listener
```

This command generates app/Listeners/SendOtpListener.php with ready-to-use code for:

* **SpeedSMS**
* **Zalo ZNS**
* **Mail**

You can also specify a custom name:

```bash
php artisan otp:make-listener MyCustomOtpSender -f
```

---

## 🧹 Cleaning Expired OTPs

Manually clean expired OTPs:

```bash
php artisan otp:clean
```

Recommended: schedule it in `app/Console/Kernel.php`:

```php
$schedule->command('otp:clean')->daily();
```

---

## 📄 License

MIT License – see the [LICENSE](LICENSE) file for details.

---

**cuongnx/laravel-flexible-otp** – A flexible, secure, and developer-friendly OTP solution for modern Laravel projects.
