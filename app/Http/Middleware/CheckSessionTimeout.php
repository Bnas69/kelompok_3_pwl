<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTimeout
{
    private const MAX_IDLE_MINUTES = 30;

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('hr_logged_in')) {
            $lastActivity = $request->session()->get('hr_last_activity', now()->timestamp);
            $idleMinutes = (now()->timestamp - $lastActivity) / 60;

            if ($idleMinutes > self::MAX_IDLE_MINUTES) {
                $request->session()->flush();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                Log::info('Session expired due to inactivity', ['ip' => $request->ip()]);
                return redirect()->route('login')->withErrors(['login' => 'Sesi berakhir karena tidak ada aktivitas. Silakan login kembali.']);
            }

            $request->session()->put('hr_last_activity', now()->timestamp);
        }

        return $next($request);
    }
}
