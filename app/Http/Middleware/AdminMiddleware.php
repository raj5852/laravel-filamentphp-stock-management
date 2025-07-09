<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (Auth::check()) {
            $authUser = auth()->user();
            $user = User::where('tenant_id', $authUser->tenant_id)->firstOrFail();

            $timezone = $user->timezone;

            // Set timezone based on user preference
            $timezoneString = match ($timezone) {
                0 => 'Asia/Dhaka',
                1 => 'Asia/Dubai',
                default => 'Asia/Dhaka',
            };

            config(['app.timezone' => $timezoneString]);
            date_default_timezone_set($timezoneString);

            if ($user->status == 0) {
                return response()->view('errors.account-disabled', [], 403);
            }
            if ($user->expires_at <= today()) {
                return response()->view('errors.subscription-expired', [], 403);
            }
        }

        return $next($request);
    }
}
