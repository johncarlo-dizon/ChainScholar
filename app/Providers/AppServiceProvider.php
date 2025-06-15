<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Document;
use App\Policies\DocumentPolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\Notification;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
    Document::class => DocumentPolicy::class,
    ];
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
    public function boot()
    {
        Blade::if('admin', function () {
            return Auth::check() && Auth::user()->role === 'admin'; // adjust according to your role logic
        });
        View::composer('*', function ($view) {
                if (Auth::check()) {
                    $userId = Auth::id();

                    $notifications = Notification::where('user_id', $userId)
                        ->latest()
                        ->take(5)
                        ->get();

                    $unreadCount = Notification::where('user_id', $userId)
                        ->where('is_read', false)
                        ->count();

                    $view->with([
                        'notifications' => $notifications,
                        'unreadCount' => $unreadCount,
                    ]);
                }
        });
    }
}
