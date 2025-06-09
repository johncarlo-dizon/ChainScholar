<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Document;
use App\Policies\DocumentPolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
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
    }
}
