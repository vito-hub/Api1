<?php

namespace App\Oauth;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CentralAuthClient
{
    public function __construct(
        protected OAuthTokenService $tokenService,
    ) {}

    public function get(string $uri, array $query = []): Response
    {
        return $this->request(
            'get',
            $uri,
            $query
        );
    }

    public function post(string $uri, array $data = []): Response
    {
        return $this->request(
            'post',
            $uri,
            $data
        );
    }

    protected function request(string $method, string $uri, array $data = []): Response
    {
        $user = Auth::user();

        if (! $user) {
            throw new RuntimeException('A locally authenticated user is required.');
        }

        $oauthToken = $user->oauthTokens()
            ->where(
                'client_id',
                config('services.oauth.client_id')
            )
            ->first();

        if (! $oauthToken) {
            throw new RuntimeException('No OAuth connection exists for this user.');
        }

        $accessToken = $this->tokenService
            ->accessToken($oauthToken);

        $response = $this->send(
            $method,
            $uri,
            $accessToken,
            $data
        );

        if ($response->status() !== 401) {
            return $response;
        }

        $accessToken = $this->tokenService
            ->forceRefresh($oauthToken);

        return $this->send(
            $method,
            $uri,
            $accessToken,
            $data
        );
    }

    protected function send(string $method, string $uri, string $accessToken, array $data = []): Response
    {
        $request = Http::withToken($accessToken)
            ->timeout(10);

        $url = rtrim(
                config('services.oauth.auth_server'),
                '/'
            ) . '/' . ltrim($uri, '/');

        return match (strtolower($method)) {
            'get'   => $request->get($url, $data),
            'post'  => $request->post($url, $data),
            default => throw new RuntimeException("Unsupported HTTP method: {$method}"),
        };
    }
}
