# cuongnx/laravel-flexible-otp

A powerful and highly flexible One-Time Password (OTP) package for Laravel applications.

**Key Features**

- **Multi-purpose configuration** – Define separate OTP settings for different use cases (login, phone verification, password reset, etc.).
- **Multi-storage support** – Works seamlessly with MySQL or MongoDB.
- **Per-purpose cooldown & rate limiting** – Prevent abuse with fine-grained control.
- **Optional automatic sending** – Built-in support for Zalo ZNS, SpeedSMS, Mail, or any custom provider.
- **Modern PHP 8+ named arguments** – Clean, readable, and type-safe API.

## Requirements

- PHP ^8.2
- Laravel ^11.0 or ^12.0

## Installation

```bash
composer require cuongnx/laravel-flexible-otp