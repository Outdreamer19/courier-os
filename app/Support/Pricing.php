<?php

namespace App\Support;

/**
 * The one place the marketing pages, signup and checkout read prices from,
 * so the numbers a courier sees always match what Stripe charges.
 */
class Pricing
{
    /**
     * @return array{monthly: float, setup: float, currency: string, founding_setup: float|null}
     */
    public static function summary(): array
    {
        return [
            'monthly' => (float) config('courieros.pricing.monthly'),
            'setup' => (float) config('courieros.pricing.setup'),
            'currency' => (string) config('courieros.pricing.currency', 'USD'),
            'founding_setup' => self::foundingSetup(),
        ];
    }

    /**
     * The discounted founding setup fee while founding spots remain, or null
     * once they are gone (or if it is not actually a discount).
     */
    public static function foundingSetup(): ?float
    {
        $founding = (float) config('courieros.marketing.founding.setup');
        $spotsLeft = (int) config('courieros.marketing.founding.spots_left');

        if ($spotsLeft <= 0 || $founding <= 0 || $founding >= (float) config('courieros.pricing.setup')) {
            return null;
        }

        return $founding;
    }
}
