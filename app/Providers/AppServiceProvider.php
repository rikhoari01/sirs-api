<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(
                $request->user()?->id ?: $request->ip()
            );
        });

        ResetPassword::createUrlUsing(function ($user, string $token) {
            return config('app.frontend_url')
                . '/reset-password'
                . '?token=' . $token
                . '&email=' . urlencode($user->email);
        });

        VerifyEmail::createUrlUsing(function ($notifiable) {
            return config('app.frontend_url') .
                '/verify-email?' .
                http_build_query([
                    'email' => Crypt::encryptString($notifiable->email),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                    'expires' => now()->addMinutes(60)->timestamp,
                ]);
        });
    }
}
