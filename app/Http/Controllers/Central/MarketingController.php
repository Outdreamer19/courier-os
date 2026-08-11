<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The CourierOS *platform* marketing pages — the product tour and the pricing
 * table. These only ever answer on the central domain; the `central`
 * middleware in routes/central.php keeps them off tenant subdomains, where
 * CourierOS branding and pricing would leak into a white-label customer's own
 * site.
 */
class MarketingController extends Controller
{
    /**
     * Annual billing is sold at a discount off twelve months of the monthly
     * price. Kept here rather than in the Vue component so the pricing page,
     * the signup summary and any future Stripe annual price stay in step.
     */
    private const ANNUAL_DISCOUNT = 0.20;

    public function product(): Response
    {
        return Inertia::render('central/Product', [
            'pricing' => $this->basePricing(),
        ]);
    }

    public function pricing(): Response
    {
        return Inertia::render('central/Pricing', [
            'pricing' => $this->annualAwarePricing(),
        ]);
    }

    /**
     * Jamaica-specific landing page: positions CourierOS for two audiences
     * at once — people starting a courier/forwarding business from scratch,
     * and existing operators running one on WhatsApp and spreadsheets.
     */
    public function jamaica(): Response
    {
        return Inertia::render('central/Jamaica', [
            'pricing' => $this->basePricing(),
        ]);
    }

    /**
     * The product page only needs the headline numbers for its inline CTAs.
     *
     * @return array{monthly: float, setup: float, currency: string}
     */
    private function basePricing(): array
    {
        /** @var array{monthly: float|int, setup: float|int, currency: string} $config */
        $config = config('courieros.pricing');

        return [
            'monthly' => (float) $config['monthly'],
            'setup' => (float) $config['setup'],
            'currency' => (string) $config['currency'],
        ];
    }

    /**
     * @return array{monthly: float, setup: float, currency: string, annualMonthly: float, annualTotal: float, annualSaving: float, annualDiscountPercent: int}
     */
    private function annualAwarePricing(): array
    {
        $base = $this->basePricing();

        // Round the effective monthly rate to whole currency units so the
        // toggle never shows a price like $63.20 that Stripe can't bill.
        $annualMonthly = round($base['monthly'] * (1 - self::ANNUAL_DISCOUNT));

        return [
            ...$base,
            'annualMonthly' => $annualMonthly,
            'annualTotal' => $annualMonthly * 12,
            'annualSaving' => ($base['monthly'] * 12) - ($annualMonthly * 12),
            'annualDiscountPercent' => (int) round(self::ANNUAL_DISCOUNT * 100),
        ];
    }
}
