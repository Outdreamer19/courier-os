<?php

namespace App\Support\Tenancy;

/**
 * Resolves tenant-specific configuration with a fallback to the platform
 * defaults in config/courieros.php (and legacy config/shipdjm.php). Every
 * value that used to be a global config constant routes through here so a
 * single courier instance and a multi-tenant instance behave consistently.
 */
class TenantConfig
{
    public function __construct(private readonly TenantManager $tenants) {}

    public function currency(): string
    {
        return $this->tenants->current()?->currency
            ?? (string) config('courieros.currency', config('shipdjm.currency', 'USD'));
    }

    public function customerReferencePrefix(): string
    {
        return $this->tenants->current()?->customer_reference_prefix
            ?? (string) config('shipdjm.customer_reference.prefix', 'CUS');
    }

    public function packageReferencePrefix(): string
    {
        return $this->tenants->current()?->package_reference_prefix
            ?? (string) config('shipdjm.package_reference.prefix', 'PKG');
    }

    public function name(): string
    {
        return $this->tenants->current()?->name
            ?? (string) config('app.name', 'CourierOS');
    }

    public function whatsappNumber(): ?string
    {
        return $this->tenants->current()?->whatsapp_number;
    }

    public function whatsappApiToken(): ?string
    {
        return $this->tenants->current()?->whatsapp_api_token
            ?? (config('services.whatsapp.api_token') ?: null);
    }

    public function whatsappPhoneNumberId(): ?string
    {
        return $this->tenants->current()?->whatsapp_phone_number_id
            ?? (config('services.whatsapp.phone_number_id') ?: null);
    }

    public function hasWhatsAppApi(): bool
    {
        return filled($this->whatsappApiToken()) && filled($this->whatsappPhoneNumberId());
    }

    public function logoPath(): ?string
    {
        return $this->tenants->current()?->logo_path;
    }

    public function brandPrimaryColor(): ?string
    {
        return $this->tenants->current()?->brand_primary_color;
    }

    public function brandAccentColor(): ?string
    {
        return $this->tenants->current()?->brand_accent_color;
    }

    public function defaultRatePerLb(): float
    {
        return (float) config('courieros.default_rate_per_lb', config('shipdjm.default_rate_per_lb', 500));
    }
}
