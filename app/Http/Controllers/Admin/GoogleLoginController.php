<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Providers\RouteServiceProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class GoogleLoginController extends Controller
{
    public function redirectToGoogle(): Response
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(): Response
    {
        try {
            /** @var Admin $user */
            $user = Socialite::driver('google')->user();

            /** @var Admin|null $existingUser */
            $existingUser = Admin::where('google_id', $user->id)->first();

            if ($existingUser) {
                // Log in the existing user.
                Auth::guard('admin')->login($existingUser);
            } else {
                // Create a new user.
                /** @var Admin $newUser */
                $newUser = Admin::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'google_id' => $user->id,
                    'password' => bcrypt(Str::random()),
                ]);

                // Log in the new user.
                Auth::guard('admin')->login($newUser);
            }
        } catch (Exception $e) {
            Log::error($e);
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function handleLogout(Request $request): Response
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect(RouteServiceProvider::HOME);
    }
}
