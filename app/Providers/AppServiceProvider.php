<?php

namespace App\Providers;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

/**
 * Core application service provider.
 *
 * Bootstraps Eloquent strict mode, registers the `api` and `auth` rate limiters,
 * and wires up authentication event listeners for activity logging.
 */
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
        // Rate limiting for login endpoint: 5 attempts per minute per email
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email', $request->ip()));
        });

        // Rate limiting for API: 60 requests per minute per authenticated user or IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });

        Event::listen(function (Login $event): void {
            $request = request();

            $activity = activity('auth')
                ->event('login')
                ->withProperties([
                    'guard' => $event->guard,
                    'ip' => $request->ip(),
                    'user_agent' => (string) $request->userAgent(),
                ]);

            if ($event->user instanceof Model) {
                $activity
                    ->causedBy($event->user)
                    ->performedOn($event->user);
            } else {
                $activity->causedBy($event->user->getAuthIdentifier());
            }

            $activity->log('auth.login');
        });

        Event::listen(function (Failed $event): void {
            $request = request();

            $activity = activity('auth')
                ->event('failed')
                ->withProperties([
                    'guard' => $event->guard,
                    'email' => $event->credentials['email'] ?? null,
                    'ip' => $request->ip(),
                    'user_agent' => (string) $request->userAgent(),
                ]);

            if ($event->user instanceof Model) {
                $activity
                    ->causedBy($event->user)
                    ->performedOn($event->user);
            } elseif ($event->user !== null) {
                $activity->causedBy($event->user->getAuthIdentifier());
            }

            $activity->log('auth.failed');
        });

        Event::listen(function (Logout $event): void {
            $request = request();

            $activity = activity('auth')
                ->event('logout')
                ->withProperties([
                    'guard' => $event->guard,
                    'ip' => $request->ip(),
                    'user_agent' => (string) $request->userAgent(),
                ]);

            if ($event->user instanceof Model) {
                $activity
                    ->causedBy($event->user)
                    ->performedOn($event->user);
            } elseif ($event->user !== null) {
                $activity->causedBy($event->user->getAuthIdentifier());
            }

            $activity->log('auth.logout');
        });
    }
}
