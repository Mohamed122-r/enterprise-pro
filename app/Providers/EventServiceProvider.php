<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Events\UserRegistered::class => [
            \App\Listeners\SendWelcomeNotification::class,
        ],
        \App\Events\InvoiceSent::class => [
            \App\Listeners\SendInvoiceNotification::class,
        ],
    ];

    public function shouldDiscoverEvents()
    {
        return false;
    }
}