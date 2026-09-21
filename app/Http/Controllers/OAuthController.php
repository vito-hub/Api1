<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Oidc\OidcTokenVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Random\RandomException;

class OAuthController extends Controller
{
    public function __construct(
        protected OidcTokenVerifier $OidcTokenVerifier,
    ){
        $this->OidcTokenVerifier = $OidcTokenVerifier;
    }
    /**
     * @throws RandomException
     */
    public function redirect(Request $request): \Illuminate\Http\RedirectResponse
    {
        $state = bin2hex(random_bytes(32));
        $nonce = bin2hex(random_bytes(32));

        $codeVerifier = rtrim(
            strtr(
                base64_encode(random_bytes(32)),
                '+/',
                '-_'
            ),
            '='
        );

        $codeChallenge = rtrim(
            strtr(
                base64_encode(
                    hash('sha256', $codeVerifier, true)
                ),
                '+/',
                '-_'
            ),
            '='
        );

        $request->session()->put([
            'oauth_state'         => $state,
            'oauth_nonce'         => $nonce,
            'oauth_code_verifier' => $codeVerifier,
        ]);

        return redirect()->away(
            config('services.oauth.auth_server')
            . '/oauth/authorize?'
            . http_build_query([
                'client_id'             => config('services.oauth.client_id'),
                'redirect_uri'          => config('services.oauth.redirect_uri'),
                'response_type'         => 'code',
                'scope'                 => 'openid profile email user-read',
                'state'                 => $state,
                'nonce'                 => $nonce,
                'code_challenge'        => $codeChallenge,
                'code_challenge_method' => 'S256',
            ])
        );
    }
    public function callback(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $expectedState  = $request->session()->pull('oauth_state');
        $nonce          = $request->session()->pull('oauth_nonce');
        $codeVerifier   = $request->session()->pull('oauth_code_verifier');

        if (
            !$expectedState ||
            !$request->filled('state') ||
            !hash_equals(
                $expectedState,
                $request->string('state')->toString()
            )
        ) {
            abort(419, 'Invalid OAuth state.');
        }

        if ($request->has('error')) {
            abort(
                403,
                $request->get(
                    'error_description',
                    'OAuth authorization failed.'
                )
            );
        }

        if (!$nonce) {
            abort(419, 'OAuth nonce is missing.');
        }

        if (!$codeVerifier) {
            abort(419, 'OAuth code verifier is missing.');
        }

        if (!$request->filled('code')) {
            abort(400, 'Authorization code is missing.');
        }

        $tokenResponse = Http::asForm()->post(
            config('services.oauth.auth_server') . '/oauth/token',
            [
                'grant_type'    => 'authorization_code',
                'client_id'     => config('services.oauth.client_id'),
                'client_secret' => config('services.oauth.client_secret'),
                'redirect_uri'  => config('services.oauth.redirect_uri'),
                'code'          => $request->code,
                'code_verifier' => $codeVerifier,
                'nonce'         => $nonce,
            ]
        );

        if ($tokenResponse->failed()) {
            return response()->json([
                'message' => 'Token unauthorized',
                'response' => $tokenResponse->json(),
            ], 401);
        }

        $tokens = $tokenResponse->json();

        if (empty($tokens['access_token'])) {
            abort(401, 'OAuth access token was not returned.');
        }

        $userRespone = $this->OidcTokenVerifier->verify($tokens['id_token'],$nonce);

        $user = User::updateOrCreate(
            [
                'auth_user_id' => $userRespone->sub,
            ],
            [
                'name'              => $userRespone->name,
                'phone'             => $userRespone->phone,
                'email'             => $userRespone->email ?? null,
                'email_verified_at' => $userRespone->email_verified_at ?? null,
            ]
        );

        $user->oauthTokens()->updateOrCreate(
            [
                'client_id' => config('services.oauth.client_id'),
            ],
            [
                'provider'                  => 'central-auth',
                'access_token'              => $tokens['access_token'],
                'refresh_token'             => $tokens['refresh_token'] ?? null,
                'access_token_expires_at'   => now()->addSeconds($tokens['expires_in']),
                'refresh_token_expires_at'  => now()->addDays(30),
            ]
        );

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function Logout()
    {
        $user = Auth::user();

        Auth::getSession()->invalidate();

        Auth::guard('web')->logout();
    }

    public function dashboard()
    {
        dd(request());
    }
    public function getUserData()
    {
        $user = Auth::user();
        $token = $user->oauthTokens()->where('client_id' , config('services.oauth.client_id'))->first();

        $response = Http::withBasicAuth(
            config('services.oauth.client_id'),
            config('services.oauth.client_secret')
        )
            ->asForm()
            ->post(
                config('services.oauth.auth_server') . '/api/oauth/introspect',
                [
                    'token' => $token->access_token,
                ]
            );

        dd([
            'status'     => $response->status(),
            'body'       => $response->body(),
            'response'   =>  $response
        ]);

    }
}
