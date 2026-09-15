<?php

namespace App\Oauth;

use App\Models\OauthToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OAuthTokenService
{
    public function accessToken(OauthToken $oauthToken): string
    {
        if ($oauthToken->access_token_expires_at->gt(now()->addMinute()))
        {
            return $oauthToken->access_token;
        }

        return $this->refreshIfNeeded($oauthToken);
    }

    public function forceRefresh(OauthToken $oauthToken): string
    {
        return $this->refreshIfNeeded(
            $oauthToken,
            force: true
        );
    }

    protected function refreshIfNeeded(OauthToken $oauthToken, bool $force = false): string
    {
        $lock = Cache::lock("oauth-token-refresh:{$oauthToken->id}", 10);

        $lock->block(5);

        try {
            $oauthToken->refresh();

            if(! $force && $oauthToken->access_token_expires_at->gt(now()->addMinute()))
            {
                return $oauthToken->access_token;
            }

            return $this->refresh($oauthToken);

        } finally {
            $lock->release();
        }
    }

    protected function refresh(OauthToken $oauthToken): string
    {
        if ($oauthToken->refreshTokenExpired()) {
            throw new RuntimeException('OAuth refresh token has expired.');
        }

        $response = Http::asForm()
            ->timeout(10)
            ->post(
                config('services.oauth.auth_server') . '/oauth/token',
                [
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => $oauthToken->refresh_token,
                    'client_id'     => config('services.oauth.client_id'),
                    'client_secret' => config('services.oauth.client_secret'),
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException('Unable to refresh OAuth access token.');
        }

        $tokens = $response->json();

        if (empty($tokens['access_token']) || empty($tokens['expires_in']))
        {
            throw new RuntimeException('OAuth server returned an invalid token response.');
        }

        $oauthToken->update([
            'access_token'              => $tokens['access_token'],
            'refresh_token'             => $tokens['refresh_token'] ?? $oauthToken->refresh_token,
            'access_token_expires_at'   => now()->addSeconds($tokens['expires_in']),
        ]);

        return $oauthToken->access_token;
    }
}
