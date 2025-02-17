<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\{TaskCreated, TaskUpdated, TaskDeleted};
use App\Listeners\{SendTaskNotification, TaskActivityListener};

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        TaskCreated::class => [
            SendTaskNotification::class,
            TaskActivityListener::class,
        ],
        TaskUpdated::class => [
            SendTaskNotification::class,
            TaskActivityListener::class,
        ],
        TaskDeleted::class => [
            SendTaskNotification::class,
            TaskActivityListener::class,
        ],
        TaskCreated::class => [
            TaskActivityListener::class . '@handleTaskCreated',
        ],
        TaskUpdated::class => [
            TaskActivityListener::class . '@handleTaskUpdated',
        ],
        TaskDeleted::class => [
            TaskActivityListener::class . '@handleTaskDeleted',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}