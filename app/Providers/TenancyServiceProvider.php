<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Stancl\JobPipeline\JobPipeline;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Listeners;
use Stancl\Tenancy\Middleware;

class TenancyServiceProvider extends ServiceProvider
{
    public static string $controllerNamespace = '';

    public function events(): array
    {
        return [
            Events\CreatingTenant::class => [],
            Events\TenantCreated::class => [
                JobPipeline::make([
                    Jobs\CreateDatabase::class,
                    Jobs\MigrateDatabase::class,
                ])->send(function (Events\TenantCreated $event) {
                    return $event->tenant;
                })->shouldBeQueued(false),
            ],
            Events\SavingTenant::class => [],
            Events\TenantSaved::class => [],
            Events\UpdatingTenant::class => [],
            Events\TenantUpdated::class => [],
            Events\DeletingTenant::class => [],
            Events\TenantDeleted::class => [
                JobPipeline::make([
                    Jobs\DeleteDatabase::class,
                ])->send(function (Events\TenantDeleted $event) {
                    return $event->tenant;
                })->shouldBeQueued(false),
            ],
            Events\DatabaseCreated::class => [],
            Events\DatabaseMigrated::class => [],
            Events\DatabaseSeeded::class => [],
            Events\DatabaseRolledBack::class => [],
            Events\DatabaseDeleted::class => [],
            Events\InitializingTenancy::class => [],
            Events\TenancyInitialized::class => [
                Listeners\BootstrapTenancy::class,
            ],
            Events\EndingTenancy::class => [],
            Events\TenancyEnded::class => [
                Listeners\RevertToCentralContext::class,
            ],
            Events\TenantNotFound::class => [],
        ];
    }

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->bootEvents();
        $this->makeTenancyMiddlewareHighestPriority();

        // Register tenant routes after all providers have booted so that
        // central domain routes (from routes/web.php) are registered first.
        // This ensures central routes take precedence over tenant routes when
        // both match the same path (e.g. GET /login on localhost).
        $this->app->booted(function () {
            $this->mapRoutes();
        });
    }

    protected function bootEvents(): void
    {
        foreach ($this->events() as $event => $listeners) {
            foreach (array_unique($listeners) as $listener) {
                if ($listener instanceof JobPipeline) {
                    Event::listen($event, $listener->toListener());
                } else {
                    Event::listen($event, $listener);
                }
            }
        }
    }

    protected function mapRoutes(): void
    {
        if (file_exists(base_path('routes/tenant.php'))) {
            Route::middleware([
                'web',
                Middleware\PreventAccessFromCentralDomains::class,
                Middleware\InitializeTenancyByDomain::class,
            ])->group(base_path('routes/tenant.php'));
        }
    }

    protected function makeTenancyMiddlewareHighestPriority(): void
    {
        // Intentionally empty — tenancy middleware is applied directly in mapRoutes()
        // so it only runs for tenant domain requests, not for central domains.
    }
}
