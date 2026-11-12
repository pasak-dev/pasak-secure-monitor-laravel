<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Pasak\SecureMonitor\Services\PasakLoginThrottleService;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view("auth.login");
    }

    public function login(Request $request): Response|RedirectResponse
    {
        // 1. Periksa apakah akun / IP sedang dalam masa cooldown Pasak
        $throttle = app(PasakLoginThrottleService::class);
        $email = $request->input("email");
        $ip = $request->ip();

        if ($throttle->enabled()) {
            $status = $throttle->status($email, $ip);
            if ($status["locked"]) {
                return $throttle->renderLockoutResponse($request, $status);
            }
        }

        $credentials = $request->validate([
            "email" => ["required", "email"],
            "password" => ["required", "string"],
        ]);

        if (Auth::attempt($credentials, $request->boolean("remember"))) {
            $request->session()->regenerate();

            if ($throttle->enabled()) {
                $throttle->registerSuccess($email, $ip);
            }

            return redirect()
                ->intended(route("dashboard"))
                ->with("status", "Selamat datang kembali!");
        }

        // 2. Jika kegagalan login ini mencapai batas toleransi (mis. 3x gagal)
        if ($throttle->enabled()) {
            $postStatus = $throttle->status($email, $ip);
            if ($postStatus["locked"]) {
                return $throttle->renderLockoutResponse($request, $postStatus);
            }
        }

        return back()
            ->withErrors(["email" => "Email atau kata sandi salah."])
            ->onlyInput("email");
    }

    public function showRegister(): View
    {
        return view("auth.register");
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            "name" => ["required", "string", "max:255"],
            "email" => ["required", "email", "max:255", "unique:users,email"],
            "password" => ["required", "string", "min:8", "confirmed"],
        ]);

        $user = User::create($validated);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route("dashboard")
            ->with("status", "Akun berhasil dibuat. Selamat datang!");
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route("posts.index")
            ->with("status", "Anda telah keluar.");
    }
}
