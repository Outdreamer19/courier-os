<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string}>
     */
    public static function publicRouteNamesProvider(): array
    {
        return [
            'home' => ['home'],
            'about' => ['about'],
            'rates' => ['rates'],
            'contact' => ['contact'],
            'terms' => ['legal.terms'],
            'privacy' => ['legal.privacy'],
            'shipping policy' => ['legal.shipping'],
            'refund policy' => ['legal.refund'],
            'restricted items' => ['legal.restricted'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function publicLegalAliasesProvider(): array
    {
        return [
            'terms alias' => ['/terms', '/legal/terms'],
            'privacy alias' => ['/privacy', '/legal/privacy'],
            'shipping alias' => ['/shipping-policy', '/legal/shipping'],
            'refund alias' => ['/refund-policy', '/legal/refund'],
            'restricted alias' => ['/restricted-items', '/legal/restricted-items'],
        ];
    }

    #[DataProvider('publicRouteNamesProvider')]
    public function test_public_routes_resolve_for_guests(string $routeName): void
    {
        $this->get(route($routeName))->assertOk();
    }

    #[DataProvider('publicLegalAliasesProvider')]
    public function test_legal_aliases_redirect_to_canonical_routes(string $from, string $to): void
    {
        $this->get($from)->assertRedirect($to);
    }
}
