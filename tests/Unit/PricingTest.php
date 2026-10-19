<?php

namespace Tests\Unit;

use App\Support\Pricing;
use Tests\TestCase;

class PricingTest extends TestCase
{
    public function test_summary_includes_the_founding_discount(): void
    {
        config([
            'courieros.pricing.monthly' => 63,
            'courieros.pricing.setup' => 149,
            'courieros.marketing.founding.setup' => 99,
            'courieros.marketing.founding.spots_left' => 5,
        ]);

        $summary = Pricing::summary();

        $this->assertSame(63.0, $summary['monthly']);
        $this->assertSame(149.0, $summary['setup']);
        $this->assertSame(99.0, $summary['founding_setup']);
    }

    public function test_founding_price_disappears_when_spots_run_out(): void
    {
        config(['courieros.marketing.founding.spots_left' => 0]);

        $this->assertNull(Pricing::foundingSetup());
    }

    public function test_founding_price_must_be_a_real_discount(): void
    {
        config([
            'courieros.marketing.founding.spots_left' => 5,
            'courieros.marketing.founding.setup' => 149,
            'courieros.pricing.setup' => 149,
        ]);

        $this->assertNull(Pricing::foundingSetup());
    }
}
