# Nxtey SSO Client

<p align="center">
    <a href="https://packagist.org/packages/nxtey/laravel-sso-client"><img src="https://img.shields.io/packagist/v/nxtey/laravel-sso-client" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/nxtey/laravel-sso-client"><img src="https://img.shields.io/packagist/l/nxtey/laravel-sso-client" alt="License"></a>
    <img src="https://img.shields.io/badge/Laravel-10%20%7C%2011-FF2D20" alt="Laravel 10/11">
    <img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4" alt="PHP 8.1+">
</p>

A **zero-configuration, auto-discovering Laravel package** that adds "Sign in with Nxtey" Single Sign-On (SSO) to any Laravel 10/11 application — including third-party, multi-vendor scripts — **without modifying a single line of their core code**.

Built on top of **Laravel Socialite** and **OAuth2** (via the central `nxtey/laravel-sso-server`), this package works exactly like "Sign in with Google" but for your own network of subdomains.

---

## 🌟 Features

- ✅ **Zero Core Alterations** — Works on any third-party Laravel script without editing their auth files
- ✅ **Auto-Discovery** — No manual provider registration needed
- ✅ **Dynamic Model Resolution** — Automatically detects the host app's `User` model
- ✅ **Schema-Safe** — Checks for `email_verified_at` column before writing to it
- ✅ **Interactive Installer** — `php artisan nxtey:sso:install` configures everything
- ✅ **Secure by Default** — Generates cryptographically random local passwords to prevent local login bypass
- ✅ **Global Logout** — Revokes Passport tokens across the entire Nxtey network
- ✅ **Session Fixation Protection** — Regenerates sessions on SSO callback
- ✅ **Vendor UI Interception** — Optional Blade component to hijack login/register buttons

---

## 📋 Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x
- A registered OAuth client on the central SSO server (`nxtey/laravel-sso-server`)

---

## 🚀 Installation

### Step 1: Install via Composer
composer require nxtey/laravel-sso-client

Step 2: Run the Interactive Installer
php artisan nxtey:sso:install

The installer will ask you:
Central SSO Server URL (default: https://auth.nxtey.com)
OAuth Client ID (from the server's admin panel)
OAuth Client Secret (from the server's admin panel)
Redirect URI (default: https://yourdomain.com/sso/callback)
It will automatically update your .env file with these values.
Step 3: (Optional) Clear Config Cache 1

⚙️ Configuration
The installer writes these variables to your .env file:
NXTEY_SERVER_URL=https://auth.nxtey.com
NXTEY_CLIENT_ID=your-client-id
NXTEY_CLIENT_SECRET=your-client-secret
NXTEY_REDIRECT_URI=https://yourdomain.com/sso/callback
NXTEY_LOGIN_REDIRECT_PATH=/dashboard

You can also publish the config file for advanced customization:
php artisan vendor:publish --tag=nxtey-sso-config
This creates config/nxtey-sso.php which you can edit directly.

🎯 Usage
Login
Direct users to:
https://yourdomain.com/s

This redirects them to the central SSO server. After authentication, they are returned to your app and automatically logged in.
Logout
Direct users to:
https://yourdomain.com/sso/logout

This clears the local session and revokes the Passport token on the central server, logging them out of the entire Nxtey network.
Callback
The /sso/callback route is handled automatically — do not register it yourself.
🔌 Intercepting Third-Party Vendor UI
Most third-party scripts have their own login/register buttons. To redirect them to SSO without editing their Blade files, include this component in their main layout (e.g., resources/views/layouts/app.blade.php) just before </body>:
@if(auth()->guest())
    @
This JavaScript snippet automatically hijacks any <a> or <form> pointing to /login, /register, /forgot-password, or /logout and redirects them to the SSO endpoints.

🛠️ How It Works
User visits /sso/login → Redirected to auth.nxtey.com/oauth/authorize
User authenticates on central server → Server redirects back to /sso/callback?code=...
Package exchanges code for token → Calls auth.nxtey.com/oauth/token
Package fetches user data → Calls auth.nxtey.com/api/user with Bearer token
Package provisions local user → Uses User::updateOrCreate() with a random password
User is logged in → Auth::login($user, true) + session regeneration
Why Random Passwords?
The package generates a Str::random(32) hashed password for each user. This ensures:
Users cannot log in locally with a password (bypassing SSO)
Even if the local DB is compromised, the attacker cannot reverse-engineer the central password
All authentication is funneled through the central SSO gateway

🔒 Security
OAuth2 PKCE-ready — Uses Laravel Socialite's secure implementation
HTTPS Required — OAuth2 will fail over plain HTTP
Session Fixation Protection — Session::regenerate() on every callback
CSRF Protection — Built into Socialite's state parameter
No Password Storage — Local passwords are random and irreversible

🐛 Troubleshooting
"Class not found" errors
Run composer dump-autoload to regenerate the autoloader.
"Invalid client" or "redirect_uri mismatch"
Ensure the redirect URI in your .env exactly matches the one registered on the SSO server's admin panel (including https:// and trailing path).
"SQL error: column email_verified_at not found"
This should not happen — the package auto-detects missing columns. If it does, please open an issue.
"User is created but not logged in"
Check that your auth.php guard is using the standard eloquent provider. The package uses Auth::guard()->getProvider()->getModel() to resolve the User class.

📁 Package Structure
nxtey/laravel-sso-client/
├── composer.json
├── config/
│   └── nxtey-sso.php
├── src/
│   ├── Console/
│   │   └── InstallCommand.php
│   ├── Http/
│   │   └── Controllers/
│   │       └── SsoController.php
│   ├── routes/
│   │   └── web.php
│   ├── views/
│   │   └── components/
│   │       └── sso-interceptor.blade.php
│   ├── NxteySocialiteProvider.php
│   └── SsoClientServiceProvider.php

🤝 Related Packages
nxtey/laravel-sso-server — The central authentication server this client connects to.

📄 License
The MIT License (MIT). Please see License File for more information.

💬 Support
For issues, feature requests, or questions, please open an issue on the GitHub repository.
