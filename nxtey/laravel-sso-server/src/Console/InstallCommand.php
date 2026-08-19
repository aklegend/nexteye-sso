<?php

namespace Nxtey\SsoServer\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class InstallCommand extends Command
{
    protected $signature = 'nxtey:sso:server:install {--admin-email= : Email for the first admin user} {--admin-password= : Password for the first admin user}';
    protected $description = 'Install and configure the Nxtey SSO Server automatically';

    public function handle()
    {
        $this->info('🚀 Starting Nxtey SSO Server Installation...');

        // 1. Publish assets
        $this->info('📦 Publishing configurations and migrations...');
        $this->callSilent('vendor:publish', ['--tag' => 'sso-server-config']);
        $this->callSilent('vendor:publish', ['--tag' => 'sso-server-migrations']);

        // 2. Run migrations
        $this->info('🗄️ Running migrations...');
        Artisan::call('migrate');

        // 3. Install Passport
        $this->info('🔑 Installing Laravel Passport...');
        Artisan::call('passport:install');

        // 4. Create Admin User
        $this->info('👤 Setting up the first Administrator...');
        $email = $this->option('admin-email') ?: $this->ask('Enter admin email');
        $password = $this->option('admin-password') ?: $this->secret('Enter admin password');

        // Ensure the User model has the is_admin column (added by our migration)
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'SSO Administrator',
                'password' => Hash::make($password),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->info('✅ Installation Complete!');
        $this->table(['Admin Email', 'Password'], [[$email, $password]]);
        $this->info('🌐 You can now log in at: ' . url('/login'));
        $this->info('🛡️ Access the admin panel at: ' . url('/admin/clients'));
    }
}