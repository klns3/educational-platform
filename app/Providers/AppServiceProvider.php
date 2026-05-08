<?php

namespace App\Providers;

use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\View;
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
        if (! $this->shouldUseViteHotReload()) {
            Vite::useHotFile(storage_path('framework/vite.hot.disabled'));
        }

        View::composer('layouts.navigation', function ($view) {
            $unreadMessagesCount = 0;
            $unreadNotificationsCount = 0;

            if (Auth::check()) {
                $unreadMessagesCount = Message::where('recipient_id', Auth::id())
                    ->where('is_read', false)
                    ->count();

                $unreadNotificationsCount = Auth::user()
                    ->notifications()
                    ->where('is_read', false)
                    ->where(function ($query) {
                        $query->whereNull('type')
                            ->orWhere('type', 'system');
                    })
                    ->count();
            }

            $view->with([
                'unreadMessagesCount' => $unreadMessagesCount,
                'unreadNotificationsCount' => $unreadNotificationsCount,
            ]);
        });
    }

    protected function shouldUseViteHotReload(): bool
    {
        if ($this->app->runningInConsole()) {
            return true;
        }

        $host = request()->getHost();

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }
}
