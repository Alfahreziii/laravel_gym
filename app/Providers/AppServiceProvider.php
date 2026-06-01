<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\View\Composers\NavbarComposer;
use App\Http\View\Composers\TrainerNavbarComposer;
use Illuminate\Support\Facades\View;
use App\Models\Trainer;
use App\Observers\TrainerObserver;
use App\Models\KehadiranMember;
use App\Observers\KehadiranMemberObserver;


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
        View::composer('components.navbar', NavbarComposer::class);
        View::composer('components.navbar', TrainerNavbarComposer::class);
        Trainer::observe(TrainerObserver::class);
        KehadiranMember::observe(KehadiranMemberObserver::class);
    }
}
