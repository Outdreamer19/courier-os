<?php

namespace App\Support;

class WhatsappLink
{
    /**
     * Build a WhatsApp click-to-chat URL for the given phone number.
     */
    public static function forPhone(?string $phone, ?string $message = null): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if ($digits === '') {
            return null;
        }

        $defaultCountry = (string) config('shipdjm.whatsapp.default_country_code', '1');

        if (strlen($digits) === 10 && $defaultCountry !== '') {
            $digits = $defaultCountry.$digits;
        }

        $url = 'https://wa.me/'.$digits;

        if ($message) {
            $url .= '?text='.rawurlencode($message);
        }

        return $url;
    }
}
