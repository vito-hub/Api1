<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
//    public function login(Request $request)
//    {
//        $credentials = $request->validate([
//            'phone' => 'required|string',
//        ]);
//
//        $response = Http::asForm()->post(
//            config('services.passport.auth_server_url') . '/api/check-phone',
//            ['phone' => $credentials['phone']]
//        );
//
//        if ($response->failed()) {
//            return back()->withErrors(['phone' => 'خطا در ورود، دوباره تلاش کنید']);
//        }
//
//        $data = $response->json();
//
////        session([
////            'access_token' => $data['access_token'],
////        ]);
//
//        if ($data['is_new_account']) {
//            return redirect('/complete-profile');
//        }
//
//        return redirect('/');
//    }
    public function checkPhone(Request $request)
    {
        $data = $request->validate(['phone' => 'required|string']);

        $response = Http::asForm()->post(
            config('services.passport.auth_server_url') . '/api/check-phone',
            ['phone' => $data['phone']]
        );

        if ($response->failed()) {
            return back()->withErrors(['phone' => 'خطا، دوباره تلاش کنید']);
        }

        $exists = $response->json('exists');

        session([
            'pending_phone' => $data['phone']
        ]);

        if ($exists) {
            return redirect('/login/password');
        }

        return redirect('/login/verify-otp');
    }

    // AuthController.php
    public function verifyPassword(Request $request)
    {
        $phone = session('pending_phone');
        if (! $phone) {
            return redirect('/login');
        }

        $data = $request->validate([
            'password' => 'required|string'
        ]);

        $response = Http::asForm()->post(
            config('services.passport.auth_server_url') . '/api/phone-login',
            [
                'phone'     => $phone,
                'password'  => $data['password'],
            ]
        );

        if ($response->failed()) {
            return back()->withErrors([
                'password' => 'پسورد اشتباه است'
            ]);
        }

        session([
            'access_token' => $response->json('access_token'),
        ]);
        session()->forget('pending_phone');

        return 'injaaaaaaaaaaa';
    }
}
