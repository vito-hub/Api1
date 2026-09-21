<?php

namespace App\Oidc;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OidcTokenVerifier
{
    /**
     * @throws RequestException
     */
    public function verify(string $idToken, string $expectedNonce): object
    {
        $parts = explode('.', $idToken);

        if (count($parts) !== 3) {
            throw new RuntimeException('Invalid ID token.');
        }

        $header = json_decode(
            base64_decode(strtr($parts[0], '-_', '+/')),
            true
        );

        if (!is_array($header)) {
            throw new RuntimeException('Invalid ID token header.');
        }

        if (($header['alg'] ?? null) !== 'RS256') {
            throw new RuntimeException('Unsupported ID token algorithm.');
        }

        $kid = $header['kid'] ?? null;

        if (!$kid) {
            throw new RuntimeException('ID token key ID is missing.');
        }

        $jwks = Http::get(
            config('services.oauth.auth_server') . '/.well-known/jwks.json'
        )->throw()->json();

        $key = collect($jwks['keys'] ?? [])
            ->firstWhere('kid', $kid);

        if (!$key) {
            throw new RuntimeException('ID token signing key not found.');
        }

        $claims = JWT::decode(
            $idToken,
            JWK::parseKeySet([
                'keys' => [$key],
            ])
        );

        if (($claims->iss ?? null) !== config('services.oauth.issuer')) {
            throw new RuntimeException('Invalid ID token issuer.');
        }

        $audience = $claims->aud ?? null;

        if (is_array($audience)) {
            $validAudience = in_array(
                config('services.oauth.client_id'),
                $audience,
                true
            );
        } else {
            $validAudience = $audience === config('services.oauth.client_id');
        }

        if (!$validAudience) {
            throw new RuntimeException('Invalid ID token audience.');
        }

        if (($claims->nonce ?? null) !== $expectedNonce) {
            throw new RuntimeException('Invalid ID token nonce.');
        }

        return $claims;
    }
}
