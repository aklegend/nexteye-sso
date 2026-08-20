# Nxtey SSO Server

<p align="center">
    <a href="https://packagist.org/packages/nxtey/laravel-sso-server"><img src="https://img.shields.io/packagist/v/nxtey/laravel-sso-server" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/nxtey/laravel-sso-server"><img src="https://img.shields.io/packagist/l/nxtey/laravel-sso-server" alt="License"></a>
    <img src="https://img.shields.io/badge/Laravel-10%20%7C%2011-FF2D20" alt="Laravel 10/11">
    <img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4" alt="PHP 8.1+">
</p>

A **fully automated, production-grade central authentication server** for the Nxtey network. Built on **Laravel Passport** (OAuth2), this package transforms any fresh Laravel installation into a complete Identity Provider (IdP) with a beautiful admin panel, CLI tools, and global logout support.

Pair this with `nxtey/laravel-sso-client` on your subdomain apps to enable "Sign in with Nxtey" across your entire network.

---

## 🌟 Features

- ✅ **One-Command Install** — `php artisan nxtey:sso:server:install` sets up everything
- ✅ **Admin Panel** — Beautiful UI to manage OAuth clients and users
- ✅ **OAuth2 Provider** — Powered by Laravel Passport with secure token lifespans
- ✅ **Global Logout** — Revokes tokens across all connected client apps
- ✅ **Admin Middleware** — Protects the admin panel with `is_admin` flag
- ✅ **Auto-Publishing** — Migrations, configs, and views are published automatically
- ✅ **Token Lifespans** — 1-day access tokens, 30-day refresh tokens (configurable)

---

## 📋 Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x
- A database (MySQL, PostgreSQL, SQLite, etc.)
- A frontend scaffolding for login UI (e.g., Laravel Breeze)

---

## 🚀 Installation

### Step 1: Create a Fresh Laravel Application

composer create-project laravel/laravel auth-server
cd auth-server

Step 2: Configure Your .env
APP_URL=https://auth.nxtey.com
APP_NAME="Nxtey Auth"

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nxtey_auth
DB_USERNAME=root
DB_PASSWORD=

SESSION_DOMAIN=.nxtey.com
SANCTUM_STATEFUL_DOMAINS=auth.nxtey.com

Step 3: Install the SSO Server Package
composer
Step 4: Run the Automated Installer
php artisan nxtey:sso:server:install

The installer will:
Publish configs and migrations
Run database migrations
Install Laravel Passport (creates encryption keys + OAuth clients)
Interactively ask for admin credentials
Create the first admin user

Step 5: Install a Frontend for Login UI
composer require laravel/breeze --dev

Step 6: Register the Admin Middleware
For Laravel 11 (bootstrap/app.php):

->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \Nxtey\SsoServer\Http\Middleware\EnsureIsAdmin::class,
    ]);
})

For Laravel 10 (app/Http/Kernel.php):

protected $middlewareAliases = [
    // ...
    'admin' => \Nxtey\SsoServer\Http\Middleware\EnsureIsAdmin::class,
];

Step 8: Configure the API Guard
In config/auth.php:

'guards' => [
    'api' => [
        'driver' => 'passport',
        'provider' => 'users',
    ],
],

🎯 Usage
Access the Admin Panel
Log in at https://auth.nxtey.com/login with the admin credentials you created.
Visit https://auth.nxtey.com/admin/clients to manage OAuth clients.
Register a New Client App
In the admin panel, fill out:
App Name: e.g., App 1 Subdomain
Redirect URI: e.g., https://app1.nxtey.com/sso/callback (must match exactly)
Click Create Client and copy the Client Secret immediately — it will never be shown again.
Provide Credentials to Client Apps
Give the client app developer:
Server URL: https://auth.nxtey.com
Client ID: (shown in admin panel)
Client Secret: (shown once at creation)
Redirect URI: (must match what you registered)
They will run php artisan nxtey:sso:install and paste these values.
Global Logout Endpoint
Client apps redirect users here to log out of the entire network:

https://auth.nxtey.com/sso/logout?return_to=https://app1.nxtey.com
This revokes the Passport token and destroys the server session.

🛠️ Artisan Commands
Command
Description
nxtey:sso:server:install
Full automated installation (migrations, Passport, admin user)
passport:install
(Built-in) Re-create Passport encryption keys if needed
passport:client
(Built-in) Create OAuth clients via CLI

🔒 Security
HTTPS Required — OAuth2 will not work over plain HTTP
Short-Lived Tokens — Access tokens expire in 1 day by default
Refresh Token Rotation — 30-day refresh tokens for seamless UX
Admin Middleware — is_admin flag prevents unauthorized access to the panel
Token Revocation — Global logout immediately invalidates tokens
CSRF Protection — Built into Laravel's session handling

📁 Package Structure
nxtey/laravel-sso-server/
├── composer.json
├── config/
│   └── sso-server.php
├── database/
│   └── migrations/
│       └── 2024_01_01_000000_add_is_admin_to_users_table.php
├── resources/
│   └── views/
│       └── admin/
│           └── clients/
│               └── index.blade.php
├── src/
│   ├── Console/
│   │   └── InstallCommand.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   └── OAuthClientController.php
│   │   │   └── SsoLogoutController.php
│   │   └── Middleware/
│   │       └── EnsureIsAdmin.php
│   ├── routes/
│   │   ├── admin.php
│   │   └── web.php
│   └── SsoServerServiceProvider.php

🌐 Full Network Architecture
┌─────────────────────────────────────────────────────────────┐
│                    auth.nxtey.com (SSO Server)              │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  Laravel Passport (OAuth2 Provider)                  │  │
│  │  - /oauth/authorize                                  │  │
│  │  - /oauth/token                                      │  │
│  │  - /api/user                                         │  │
│  │  - /sso/logout                                       │  │
│  │  - /admin/clients (Admin Panel)                      │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                              ▲
                              │ OAuth2 Flow
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
┌───────┴────────┐   ┌────────┴────────┐   ┌───────┴────────┐
│ app1.nxtey.com │   │ app2.nxtey.com  │   │ app3.nxtey.com │
│ (Client App)   │   │ (Client App)    │   │ (Client App)   │
│                │   │                 │   │                │
│ nxtey/         │   │ nxtey/          │   │ nxtey/         │
│ laravel-sso-   │   │ laravel-sso-    │   │ laravel-sso-   │
│ client         │   │ client          │   │ client         │
└────────────────┘   └─────────────────┘   └────────────────┘

🐛 Troubleshooting
"Class 'App\Models\User' not found" during install
Make sure you've run composer install and that your Laravel app has a User model.
"SQLSTATE[42S22]: Column 'is_admin' not found"
Run php artisan migrate to apply the is_admin column migration.
"The provided authorization grant is invalid"
Check that the redirect URI in the client app's .env exactly matches the one registered in the admin panel.
"Access token not found" on /api/user
Ensure the client app is sending the token in the Authorization: Bearer <token> header.

🤝 Related Packages
nxtey/laravel-sso-client — The client package installed on each subdomain app.

📄 License
The MIT License (MIT). Please see License File for more information.

💬 Support
For issues, feature requests, or questions, please open an issue on the GitHub repository.

