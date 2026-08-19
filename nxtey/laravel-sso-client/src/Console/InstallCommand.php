<?php

namespace Nxtey\SsoClient\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'nxtey:sso:install';
    protected $description = 'Install and configure the Nxtey SSO Client package';

    public function handle()
    {
        $this->info('🚀 Installing Nxtey SSO Client...');

        // 1. Publish Config
        $this->callSilent('vendor:publish', ['--tag' => 'nxtey-sso-config']);
        $this->info('✅ Configuration file published.');

        // 2. Interactive Setup
        $serverUrl = $this->ask('Enter the Central SSO Server URL', 'https://auth.nxtey.com');
        $clientId = $this->ask('Enter your OAuth Client ID');
        $clientSecret = $this->secret('Enter your OAuth Client Secret');
        $redirectUri = $this->ask('Enter Redirect URI', 'https://' . request()->getHost() . '/sso/callback');

        // 3. Update .env
        $this->updateEnv([
            'NXTEY_SERVER_URL' => $serverUrl,
            'NXTEY_CLIENT_ID' => $clientId,
            'NXTEY_CLIENT_SECRET' => $clientSecret,
            'NXTEY_REDIRECT_URI' => $redirectUri,
        ]);

        $this->info('✅ .env file updated successfully.');
        $this->info('🎉 Installation complete! Users can now log in via /sso/login');
    }

    protected function updateEnv(array $data)
    {
        $envPath = base_path('.env');
        $content = File::get($envPath);

        foreach ($data as $key => $value) {
            $value = str_replace('"', '\"', $value);
            $pattern = "/^{$key}=.*/m";
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}=\"{$value}\"", $content);
            } else {
                $content .= "\n{$key}=\"{$value}\"";
            }
        }

        File::put($envPath, $content);
    }
}