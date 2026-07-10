<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLogins
{
    private const MAX_ATTEMPTS = 5;

    private const LOCKOUT_MINUTES = 15;

    private const DECAY_SECONDS = 60;

    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->key($request);
        $attempts = (int) Cache::get($key, 0);

        if ($attempts >= self::MAX_ATTEMPTS) {
            $lockoutUntil = Cache::get($key . ':lockout');
            if ($lockoutUntil && now()->lt($lockoutUntil)) {
                $remaining = now()->diffInSeconds($lockoutUntil);
                Log::warning('Login lockout triggered', ['ip' => $request->ip(), 'username' => $request->input('username')]);
                return back()
                    ->withErrors(['login' => "Terlalu banyak percobaan login. Coba lagi dalam {$remaining} detik."])
                    ->onlyInput('username');
            }
            Cache::forget($key);
            Cache::forget($key . ':lockout');
        }

        $response = $next($request);

        if ($response->getStatusCode() === 302 && session()->has('errors')) {
            $attempts = Cache::increment($key, 1, self::DECAY_SECONDS);
            if ($attempts >= self::MAX_ATTEMPTS) {
                Cache::put($key . ':lockout', now()->addMinutes(self::LOCKOUT_MINUTES), now()->addMinutes(self::LOCKOUT_MINUTES));
                Log::warning('Login locked out', ['ip' => $request->ip(), 'username' => $request->input('username')]);
            }
        } else {
            Cache::forget($key);
            Cache::forget($key . ':lockout');
        }

        return $response;
    }

    private function key(Request $request): string
    {
        return 'login_attempts:' . $request->ip() . '|' . $request->input('username');
    }
}
