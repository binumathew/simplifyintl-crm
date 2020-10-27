<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Options;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $settings = Options::whereIn('category',['general','company'])->pluck('value','name');
        config()->set('settings', $settings);
    }
}
