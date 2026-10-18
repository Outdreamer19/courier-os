<?php

namespace App\Providers;

use App\Channels\WhatsApp\WhatsAppChannel;
use App\Models\User;
use App\Support\Demo\DemoMode;
use Carbon\CarbonImmutable;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
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
        $this->silenceDemoCourier();
    }

    /**
     * The public demo courier must never email or WhatsApp anyone: its
     * accounts are fictional and its logins are shared. Returning false from
     * these listeners cancels the send.
     */
    protected function silenceDemoCourier(): void
    {
        Event::listen(NotificationSending::class, function (NotificationSending $event): ?bool {
            return DemoMode::shouldSuppressNotificationTo($event->notifiable) ? false : null;
        });

        Event::listen(MessageSending::class, function (): ?bool {
            return DemoMode::active() ? false : null;
        });
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
