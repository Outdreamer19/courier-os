<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
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
            'refund policy' => ['legal.refund'],
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

    /**
     * @return array<string, array{string, string}>
     */
    public static function platformLegalPagesProvider(): array
    {
        return [
            'terms' => ['/legal/terms', 'central/legal/Terms'],
            'privacy' => ['/legal/privacy', 'central/legal/Privacy'],
            'refunds' => ['/legal/refund', 'central/legal/Refunds'],
        ];
    }

    #[DataProvider('platformLegalPagesProvider')]
    public function test_central_domain_serves_courieros_own_legal_pages(string $url, string $component): void
    {
        $this->get($url)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component($component, false)
                ->where('company.name', config('courieros.company.name'))
                ->where('company.number', config('courieros.company.number'))
                ->has('updated'));
    }

    public function test_courier_only_policies_redirect_to_terms_on_the_central_domain(): void
    {
        $this->get(route('legal.shipping'))->assertRedirect('/legal/terms');
        $this->get(route('legal.restricted'))->assertRedirect('/legal/terms');
    }
}
