<?php

namespace App\Providers;

use Illuminate\Auth\Events\Verified;
use App\Listeners\UpdateTrainerStatusAfterEmailVerified;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Verified::class => [
            UpdateTrainerStatusAfterEmailVerified::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}