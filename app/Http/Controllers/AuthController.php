<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\MarketplaceCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister(Request $request)
    {
        $captchaFirst = random_int(1, 9);
        $captchaSecond = random_int(1, 9);
        $request->session()->put('registration_captcha_answer', $captchaFirst + $captchaSecond);

        return view('auth.register', compact('captchaFirst', 'captchaSecond'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:merchant,customer',
            'company_name' => 'required|string|max:150',
            'phone' => 'required|string|max:30',
            'address' => 'required|string|max:1000',
            'food_type' => 'nullable|string|max:80',
            'captcha_answer' => 'required|integer|min:2|max:18',
        ], [
            'captcha_answer.required' => 'Jawaban CAPTCHA wajib diisi.',
            'captcha_answer.integer' => 'Jawaban CAPTCHA harus berupa angka.',
            'captcha_answer.min' => 'Jawaban CAPTCHA tidak sesuai.',
            'captcha_answer.max' => 'Jawaban CAPTCHA tidak sesuai.',
        ]);

        $expectedAnswer = $request->session()->pull('registration_captcha_answer');
        if ($expectedAnswer === null || ! hash_equals((string) $expectedAnswer, (string) $data['captcha_answer'])) {
            throw ValidationException::withMessages([
                'captcha_answer' => 'Jawaban CAPTCHA salah atau sudah kedaluwarsa. Silakan muat ulang formulir.',
            ]);
        }
        unset($data['captcha_answer']);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        if ($user->role === 'merchant') MarketplaceCache::invalidate();
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Akun berhasil dibuat. Selamat datang!');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau kata sandi tidak cocok.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}