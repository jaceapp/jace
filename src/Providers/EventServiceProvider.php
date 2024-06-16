<?php

namespace JaceApp\Jace\Providers;

use JaceApp\Jace\Events\LogUserMessageEvent;
use JaceApp\Jace\Listeners\LogUserMessageListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        LogUserMessageEvent::class => [
            LogUserMessageListener::class,
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}
