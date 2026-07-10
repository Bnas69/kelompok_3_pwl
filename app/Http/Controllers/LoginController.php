<?php

namespace App\Http\Controllers;

use App\Services\HrAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(private readonly HrAccountService $accounts)
    {
    }

    public function show(): View|RedirectResponse
    {
        if (session('hr_logged_in')) {
            return redirect()->route('dashboard');
        }

        return view('login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:80'],
            'password' => ['required', 'string', 'max:255'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $account = $this->accounts->attempt($credentials['username'], $credentials['password']);

        if (! $account) {
            return back()
                ->withErrors(['login' => 'Username atau password salah.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();
        $request->session()->put('hr_last_activity', now()->timestamp);
        $request->session()->put($this->accounts->sessionPayload($account));

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
