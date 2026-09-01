<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\Organization;
use App\Models\Event as EventModel;
use App\Policies\OrganizationPolicy;
use App\Policies\EventPolicy;

use Illuminate\Support\Facades\Event;
use App\Events\TransactionPaid;
use App\Listeners\GenerateTicketsAndSendConfirmation;

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
        Event::listen(
            TransactionPaid::class,
            GenerateTicketsAndSendConfirmation::class,
        );

        // Register Policies
        Gate::policy(Organization::class, OrganizationPolicy::class);
        Gate::policy(EventModel::class, EventPolicy::class);

        // Remove X-Powered-By header
        header_remove('X-Powered-By');

        // Super Admin bypass: super_admin melewati semua Policy tanpa pengecualian
        Gate::before(function ($user, $ability) {
            if ($user->role === 'super_admin') {
                return true;
            }
        });
    }
}
