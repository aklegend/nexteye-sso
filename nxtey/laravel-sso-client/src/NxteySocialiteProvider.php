<?php

namespace Nxtey\SsoClient;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;
use GuzzleHttp\Exception\GuzzleException;

class NxteySocialiteProvider extends AbstractProvider implements ProviderInterface
{
    protected string $serverUrl;

    public function __construct($request, $clientId, $clientSecret, $redirectUrl)
    {
        parent::__construct($request, $clientId, $clientSecret, $redirectUrl);
        $this->serverUrl = rtrim(config('nxtey-sso.server_url'), '/');
    }

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase($this->serverUrl . '/oauth/authorize', $state);
    }

    protected function getTokenUrl(): string
    {
        return $this->serverUrl . '/oauth/token';
    }

    /**
     * @throws GuzzleException
     */
    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get($this->serverUrl . '/api/user', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    protected function mapUserToObject(array $user): User
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ]);
    }
}