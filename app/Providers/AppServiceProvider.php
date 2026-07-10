<?php

namespace App\Providers;

use App\Channels\WhatsApp\WhatsAppChannel;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();
        $this->configureRouteBindings();
        $this->configureNotificationChannels();
    }

    protected function configureNotificationChannels(): void
    {
        $this->app->make(ChannelManager::class)->extend(
            'whatsapp',
            fn ($app) => $app->make(WhatsAppChannel::class),
        );
    }

    protected function configureRouteBindings(): void
    {
        Route::bind('customer', function (string $value): User {
            return User::query()
                ->where('role', User::ROLE_CUSTOMER)
                ->findOrFail($value);
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
