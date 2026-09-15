<?php

// app/Http/Middleware/VerifyPassportToken.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class VerifyPassportToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->session()->get('access_token');

        if (! $token) {
            return redirect('/login');
        }

        try {
            $publicKey = file_get_contents(storage_path('oauth-public.key'));
            $payload = JWT::decode($token, new Key($publicKey, 'RS256'));

            $request->attributes->set('auth_user_id', $payload->sub);
            $request->attributes->set('auth_scopes', isset($payload->scopes) ? $payload->scopes : []);
        } catch (\Exception $e) {
            $request->session()->forget('access_token');
            return redirect('/login');
        }

        return $next($request);
    }
}
