# CourierOS Lynai-Themed Landing Page Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the CourierOS central marketing site (`courieros.co`) as a single, animated, one-page landing page that faithfully recreates the visual design, layout, colour palette, typography, and scroll/motion behaviour of the [Lynai / "Saalyn" Framer template](https://lynai.framer.website/), populated with real CourierOS product content (multi-tenant courier SaaS: pre-alerts, package tracking, Stripe billing, WhatsApp notifications, branded subdomains) instead of Lynai's AI-agent copy.

**Architecture:** `courieros.co`'s existing `/` route (`PublicPageController::home()`) already branches per-tenant via `TenantManager`; we extend that branch so a **central** (no-tenant) request renders a new `central/Home` Inertia page instead of the tenant `public/Home` page. The new page is a self-contained, full-bleed Vue page (no dashboard chrome) assembled from ~14 single-purpose "marketing" components under `resources/js/components/marketing/`, styled with a new, isolated set of Tailwind v4 theme tokens (`--marketing-*`) so nothing in the existing shadcn/tenant theme is touched. Scroll-triggered reveal animation reuses the existing `useScrollReveal` composable/CSS classes already in `app.css`; a new `useCountUp` composable drives animated statistics; a CSS `@keyframes marquee` drives the auto-scrolling logo strip. Real product screenshots don't exist yet, so every screenshot slot is a reusable `ProductScreenshotFrame` component that renders a clearly-labelled placeholder until a real image path is supplied — "space for screenshots," not fake screenshots.

**Tech Stack:** Laravel 12 + Inertia.js + Vue 3 (`<script setup>` + TypeScript) + Tailwind CSS v4, existing `@vueuse/core`/`lucide-vue-next`/`reka-ui` deps, Bunny Fonts (self-hosted Google-Fonts-compatible loader already used for Instrument Sans) for Inter, PHPUnit for backend route/branch coverage.

---

## Why this page, and a content-honesty ground rule

Research (see conversation) confirmed two separate audiences already exist in this codebase:

- **Tenant consumer sites** (e.g. the existing `public/Home.vue`, "TODAY Shipping" branding, navy/red tokens) — sell shipping-to-Jamaica to end customers. **Not in scope.** Per your direction, TODAY Shipping was the pitch vehicle; going forward CourierOS itself is the product being sold.
- **The central CourierOS site** (`courieros.co`) — sells the *platform* to courier/freight-forwarding business owners. It currently has a signup form (`central/Signup.vue`) and billing pages, but **no homepage** — `config/courieros.php` even says the central domain is meant to host "the CourierOS marketing site," but nothing renders it yet; hitting `/` on the central domain today silently falls through to the tenant `public/Home` page. This plan builds that missing homepage.

**Ground rule — no fabricated social proof.** CourierOS has no live paying tenants yet. The Lynai template leans heavily on customer logos, a testimonial quote, and big vanity stats ("50M+ tasks," etc.). We will **not** invent fake customer names or usage numbers and present them as real. Concretely:
- The "trusted by" logo strip and testimonial component are built and styled to match Lynai exactly, but ship with `v-if="logos.length"` / `v-if="testimonials.length"` guards defaulting to **empty arrays** — the sections simply don't render until you have real logos/quotes to drop in as props. This keeps the page 100% honest today and is a one-line change later.
- The big stats banner uses **true-by-design capability claims** ($79/mo starting price, 24/7 automated tracking, minutes-to-launch, 100% tenant data isolation) instead of invented usage metrics.
- Pricing reflects the actual Stripe/Cashier numbers already wired up in `TenantSignupController` ($79/mo + $349 one-time setup) — not an invented 4-tier ladder.

If you'd rather ship with placeholder logos/quotes visible (clearly marked "sample"), say so during plan review — the default here is "hide until real."

## Testing approach (deviation from strict TDD, explained)

This repo has PHPUnit for the backend (`php artisan test`, `tests/Feature/**`) but **no JS test runner** (no Vitest/Jest/Playwright in `package.json`). So:
- **Backend logic** introduced by this plan (the central/tenant branch in `PublicPageController::home()`, the layout-resolution fix) gets real PHPUnit feature tests, written test-first, following the exact conventions already in `tests/Feature/Billing/TenantSignupTest.php` and `tests/Feature/Tenancy/TenantResolutionTest.php` (`config(['courieros.central_domain' => 'courieros.co'])` + `$this->get('http://courieros.co/...')` + `assertInertia(fn ($page) => $page->component(...))`).
- **Presentational Vue components** (the bulk of this plan) have no test-first cycle to run — instead each component task's "verify" step is `npm run types:check` (vue-tsc), `npm run lint:check`, `npm run build`, and a manual visual check against the Lynai reference screenshots captured during planning (Playwright/Claude-in-Chrome side-by-side comparison at 1440px and 390px). This is called out explicitly per task instead of a fake `- [ ] write failing test`.

---

## Design tokens captured from https://lynai.framer.website/ (measured via computed styles + screenshots)

| Token | Hex | Used for |
|---|---|---|
| `--marketing-cream` | `#FFFBF5` | page background |
| `--marketing-ink` | `#210D02` | headings, body text, dark section backgrounds (footer, stats banner) |
| `--marketing-ink-muted` | `#4F4D49` (72% opacity in body copy) | secondary/body text |
| `--marketing-amber` | `#FCD519` | primary CTA button background, nav "Get Started" pill |
| `--marketing-orange` | `#FA8F1F` | badges, icon chips, checkmarks, gradient accents |
| `--marketing-orange-deep` | `#FA7B31` | gradient accents, hover states |
| `--marketing-olive` | `#AA8322` | secondary chart/legend accent |
| `--marketing-periwinkle` | `#C6DFFA` | announcement bar background, active-tab background |
| `--marketing-lavender` | `#D6CDF0` | gradient panel background (approximate — sample precisely from the reference screenshot if pixel-perfect match matters) |
| `--marketing-eggshell` | `#EFECDF` | subtle alternate section background |
| `--marketing-border` | `rgba(33,13,2,0.08)` | hairline borders on cards |

Font: **Inter** (self-hosted via Bunny, same pattern as the existing Instrument Sans). Buttons: `border-radius: 8px`. Cards/panels: `border-radius: 16–24px`, soft shadow, hover lift (`translateY(-2px)` + shadow increase). Section transitions use a full-bleed horizontal gradient bar (orange → lavender/periwinkle) between sections.

Motion inventory to reproduce:
1. **Scroll-reveal** — sections/items fade + translate-y-in on first intersection (already exists as `.fade-in-section` / `.fade-in-item` in `app.css`, driven by `useScrollReveal`; reuse as-is).
2. **Sticky header** that stays pinned with a blurred/translucent background on scroll.
3. **Auto-scrolling logo marquee** (infinite horizontal loop, pauses on hover, grayscale → colour on hover per logo).
4. **Animated count-up numbers** (stat panels, stats banner) — count from 0 to target once visible.
5. **Tabbed hero visual** — a row of pill tabs, one active (periwinkle background), controls which screenshot/visual shows below; auto-rotates on an interval and is also clickable.
6. **Accordion FAQ** — one open at a time, chevron rotates, height animates.
7. **Card hover lift** on feature/pricing/testimonial cards.
8. `prefers-reduced-motion: reduce` must disable all of the above (marquee pauses, count-up jumps to final value, reveal shows immediately) — already partially handled for scroll-reveal in `app.css`; extend the same guard to the new pieces.

---

## File Structure Overview

**Backend (modified):**
- `app/Http/Controllers/PublicPageController.php` — branch `home()` to render `central/Home` when no tenant is bound.
- `resources/js/app.ts` — fix Inertia layout resolution so standalone `central/*` pages (Home, Signup, BillingSuccess, BillingCancelled) don't get wrapped in the authenticated dashboard shell.
- `resources/css/app.css` — add `--marketing-*` tokens and marquee keyframes.
- `vite.config.ts` — add Inter to the Bunny fonts list.

**Backend (new):**
- `tests/Feature/Central/MarketingHomeTest.php`

**Frontend (new):**
- `resources/js/pages/central/Home.vue` — page shell, assembles all sections, `layout: null`.
- `resources/js/composables/useCountUp.ts`
- `resources/js/components/marketing/ProductScreenshotFrame.vue`
- `resources/js/components/marketing/FeatureShowcase.vue`
- `resources/js/components/marketing/AnimatedStatPanel.vue`
- `resources/js/components/marketing/AnimatedBarChart.vue`
- `resources/js/components/marketing/AnnouncementBar.vue`
- `resources/js/components/marketing/MarketingNav.vue`
- `resources/js/components/marketing/HeroSection.vue`
- `resources/js/components/marketing/LogoMarquee.vue`
- `resources/js/components/marketing/TestimonialSwitcher.vue`
- `resources/js/components/marketing/IntegrationsGrid.vue`
- `resources/js/components/marketing/StatsBanner.vue`
- `resources/js/components/marketing/PricingSection.vue`
- `resources/js/components/marketing/FeatureGrid.vue`
- `resources/js/components/marketing/FaqAccordion.vue`
- `resources/js/components/marketing/FinalCta.vue`
- `resources/js/components/marketing/MarketingFooter.vue`

**Frontend (modified, small bug fix discovered during research):** none beyond `app.ts` above — `central/Signup.vue`, `central/BillingSuccess.vue`, `central/BillingCancelled.vue` need no code changes themselves, they just start rendering correctly once the layout-resolution fix lands.

---

## Phase 0: Baseline

### Task 0: Branch and baseline green

**Files:** none

- [ ] **Step 1:** `git checkout -b feat/courieros-marketing-landing`
- [ ] **Step 2:** Run: `php artisan test` — record pass count, confirm green before touching anything.
- [ ] **Step 3:** Run: `npm run lint:check && npm run format:check && npm run types:check` — confirm clean baseline (or record pre-existing failures so they aren't blamed on this work).

---

## Phase 1: Backend plumbing

### Task 1: Render `central/Home` for the central domain

**Files:**
- Modify: `app/Http/Controllers/PublicPageController.php:29-41`
- Test: `tests/Feature/Central/MarketingHomeTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature\Central;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingHomeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['courieros.central_domain' => 'courieros.co']);
    }

    public function test_central_domain_renders_the_marketing_home_page(): void
    {
        $this->get('http://courieros.co/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('central/Home')
                ->has('pricing')
            );
    }

    public function test_www_reserved_subdomain_also_renders_the_marketing_home_page(): void
    {
        $this->get('http://www.courieros.co/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('central/Home'));
    }

    public function test_tenant_subdomain_still_renders_the_tenant_home_page(): void
    {
        \App\Models\Tenant::factory()->create([
            'subdomain' => 'acme',
            'status' => \App\Models\Tenant::STATUS_ACTIVE,
        ]);

        $this->get('http://acme.courieros.co/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('public/Home'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=MarketingHomeTest -v`
Expected: FAIL — `central/Home` page doesn't exist yet and the controller still renders `public/Home` for every host.

> Note: check `App\Models\Tenant::STATUS_ACTIVE` and the factory's default `status` before relying on this exact snippet — if the factory already defaults to active, drop the explicit override. Match whatever `TenantFactory` actually exposes (grep `database/factories/TenantFactory.php`).

- [ ] **Step 3: Implement the branch**

```php
// app/Http/Controllers/PublicPageController.php

public function home(): Response|View
{
    $tenant = $this->tenants->current();

    if ($tenant && isset(self::CUSTOM_HOME_VIEWS[$tenant->subdomain])) {
        return view(self::CUSTOM_HOME_VIEWS[$tenant->subdomain]);
    }

    if (! $this->tenants->hasTenant()) {
        return Inertia::render('central/Home', [
            'pricing' => [
                'monthly' => 79,
                'setup' => 349,
                'currency' => 'USD',
            ],
        ]);
    }

    return Inertia::render('public/Home', [
        'rate' => $this->rates->primaryRate(),
        'rateTiers' => $this->rates->activeTiers(),
    ]);
}
```

Hard-coding `79` / `349` here duplicates what `TenantSignupController::show()` already hard-codes (see `app/Http/Controllers/Central/TenantSignupController.php:20-25`) — that's an existing pattern in the codebase, not a new one introduced here, so this plan doesn't try to "fix" it by extracting a shared config value. If you want a single source of truth, that's worth a small follow-up but is out of scope for a landing-page plan.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=MarketingHomeTest -v`
Expected: still FAIL at this point (component `central/Home` doesn't exist on the frontend yet, so Inertia rendering itself will error) — that's expected; this task only proves the backend branch. It will fully pass once Task 12 (page assembly) exists. Note that in the plan tracker rather than forcing it green here.

- [ ] **Step 5: Fix the regression this introduces in `tests/Feature/InertiaSharedDataTest.php`**

An independent review of this plan (dispatched against the live codebase) caught a real gap here, worth explaining in full: `tests/Feature/InertiaSharedDataTest.php:19` and `:34` call `$this->get(route('home'))`. Nothing in `phpunit.xml`/`config/app.php` overrides `APP_URL`, so that request goes to the default host (`localhost`). `ResolveTenant.php:36-39` treats an unrecognized host like `localhost` as central context with **no tenant bound** ("let it fall through as central so the app/marketing still works on arbitrary local hosts in dev"). Today that's harmless because `home()` always returns `public/Home` regardless of tenant. After Step 3 above, a `localhost` request with no tenant bound will hit the new `! $this->tenants->hasTenant()` branch and get `central/Home` instead — both assertions in that file (`->component('public/Home')` and the shared `rate`/`warehouse` prop checks) will fail.

Fix it by pointing these two tests at an explicit tenant subdomain host, matching the existing convention already used in `tests/Feature/Admin/ReportsTest.php:22,44` (`config(['courieros.central_domain' => 'localhost'])` + a `{subdomain}.localhost` request):

```php
// tests/Feature/InertiaSharedDataTest.php
<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Database\Seeders\ShippingRateSeeder;
use Database\Seeders\WarehouseAddressSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class InertiaSharedDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['courieros.central_domain' => 'localhost']);
        Tenant::factory()->create(['subdomain' => 'shipd']);
    }

    public function test_home_page_receives_active_rate_snapshot(): void
    {
        $this->seed([WarehouseAddressSeeder::class, ShippingRateSeeder::class]);

        $this->get('http://shipd.localhost/')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('public/Home')
                ->where('rate.currency', config('shipdjm.currency'))
                ->where(
                    'rate.rate_per_lb',
                    fn ($value) => (float) $value === (float) config('shipdjm.default_rate_per_lb'),
                )
            );
    }

    public function test_warehouse_is_shared_to_inertia_pages(): void
    {
        $this->seed([WarehouseAddressSeeder::class]);

        $this->get('http://shipd.localhost/')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('warehouse.city', 'Miami')
                ->where('warehouse.state', 'FL')
            );
    }
}
```

Before wiring this up, check whether `WarehouseAddressSeeder`/`ShippingRateSeeder` are tenant-scoped (do the seeded rows get a `tenant_id`, and do they need a tenant bound via `TenantManager::set()` *before* seeding to auto-fill it, the same way `tests/Feature/Admin/ReportsTest.php:26-32`'s `setTenant()` helper does)? If the warehouse/rate tables aren't tenant-scoped yet, the seeders as originally written are fine as-is and only the request host needs to change. If they are tenant-scoped, bind the tenant first: `app(\App\Support\Tenancy\TenantManager::class)->set(Tenant::query()->where('subdomain', 'shipd')->firstOrFail());` before the `$this->seed(...)` calls. Confirm by reading the seeder classes — don't guess.

Run: `php artisan test --filter=InertiaSharedDataTest -v`
Expected: both tests PASS against the updated host.

- [ ] **Step 6: Run the full suite to catch any other host-dependent regressions**

Run: `php artisan test`
Expected: PASS except for the still-unimplemented `MarketingHomeTest` from Step 2 (that's expected until Task 12). Grep the codebase for any other `route('home')` or bare `$this->get('/')` calls in `tests/Feature/**` that might have the same `localhost`-resolves-to-central problem — fix any found using the same pattern.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/PublicPageController.php tests/Feature/Central/MarketingHomeTest.php tests/Feature/InertiaSharedDataTest.php
git commit -m "feat: branch central domain to render the marketing home page"
```

### Task 2: Fix Inertia layout resolution for standalone central pages

**Files:**
- Modify: `resources/js/app.ts:13-26`

Currently `central/Signup.vue`, `central/BillingSuccess.vue`, and `central/BillingCancelled.vue` are full-bleed, self-styled pages, but `app.ts`'s layout switch has no `central/` case, so they fall into `default: AppLayout` — the authenticated dashboard sidebar shell. That shell expects an authenticated session and isn't meant for a public signup form. `central/admin/*` (the platform super-admin dashboard) genuinely *should* keep the dashboard shell. Fix the switch to treat `central/` (excluding `central/admin/`) as standalone.

- [ ] **Step 1: Make the change**

```typescript
// resources/js/app.ts
layout: (name) => {
    switch (true) {
        case name === 'Welcome':
            return null;
        case name.startsWith('public/'):
            return PublicLayout;
        case name.startsWith('auth/'):
            return AuthLayout;
        case name.startsWith('settings/'):
            return [AppLayout, SettingsLayout];
        case name.startsWith('central/') && !name.startsWith('central/admin/'):
            return null;
        default:
            return AppLayout;
    }
},
```

- [ ] **Step 2: Verify manually**

Run: `npm run build`, then visit `/signup` on a central-domain host (e.g. via `php artisan serve` with `COURIEROS_CENTRAL_DOMAIN` matching your local host, or Herd) and confirm the signup form renders full-bleed with no sidebar/topbar around it. Also visit `/platform` (requires a platform-owner login) and confirm the admin dashboard chrome is unchanged.
Expected: signup page full-bleed; platform admin unchanged.

- [ ] **Step 3: Commit**

```bash
git add resources/js/app.ts
git commit -m "fix: don't wrap standalone central pages in the dashboard layout"
```

---

## Phase 2: Design system foundation

### Task 3: Marketing colour tokens, marquee keyframes, Inter font

**Files:**
- Modify: `resources/css/app.css:66-74` (theme mapping), `resources/css/app.css:167-176` (`:root` raw values), `resources/css/app.css:94-155` (`@layer utilities`)
- Modify: `vite.config.ts:1-25`

- [ ] **Step 1:** Add Inter to the font loader.

```typescript
// vite.config.ts
laravel({
    input: ['resources/css/app.css', 'resources/js/app.ts'],
    refresh: true,
    fonts: [
        bunny('Instrument Sans', { weights: [400, 500, 600] }),
        bunny('Inter', { weights: [400, 500, 600, 700] }),
    ],
}),
```

- [ ] **Step 2:** Add marketing tokens to the `@theme inline` block (append after line 73, `--color-brand-cream: var(--brand-cream);`):

```css
--color-marketing-cream: var(--marketing-cream);
--color-marketing-ink: var(--marketing-ink);
--color-marketing-ink-muted: var(--marketing-ink-muted);
--color-marketing-amber: var(--marketing-amber);
--color-marketing-orange: var(--marketing-orange);
--color-marketing-orange-deep: var(--marketing-orange-deep);
--color-marketing-olive: var(--marketing-olive);
--color-marketing-periwinkle: var(--marketing-periwinkle);
--color-marketing-lavender: var(--marketing-lavender);
--color-marketing-eggshell: var(--marketing-eggshell);
--color-marketing-border: var(--marketing-border);
--font-marketing: 'Inter', 'Inter Placeholder', ui-sans-serif, sans-serif;
```

- [ ] **Step 3:** Add the raw values to `:root` (append after line 176, `--brand-cream: hsl(40 30% 97%);`):

```css
/* Marketing (CourierOS central site) tokens — isolated from the tenant/shadcn
   theme above; only consumed by resources/js/components/marketing/*. */
--marketing-cream: #fffbf5;
--marketing-ink: #210d02;
--marketing-ink-muted: rgba(79, 77, 73, 0.72);
--marketing-amber: #fcd519;
--marketing-orange: #fa8f1f;
--marketing-orange-deep: #fa7b31;
--marketing-olive: #aa8322;
--marketing-periwinkle: #c6dffa;
--marketing-lavender: #d6cdf0;
--marketing-eggshell: #efecdf;
--marketing-border: rgba(33, 13, 2, 0.08);
```

- [ ] **Step 4:** Add marquee keyframes to `@layer utilities` (append after the existing `fade-in-*` rules, before the closing brace around line 155):

```css
@keyframes marketing-marquee {
    from {
        transform: translateX(0);
    }
    to {
        transform: translateX(-50%);
    }
}

.marketing-marquee-track {
    animation: marketing-marquee 32s linear infinite;
}

.marketing-marquee-track:hover {
    animation-play-state: paused;
}

@media (prefers-reduced-motion: reduce) {
    .marketing-marquee-track {
        animation: none;
    }
}
```

- [ ] **Step 5: Verify**

Run: `npm run build` — confirm no Tailwind/PostCSS errors and that classes like `bg-marketing-cream` are available (spot-check by using one in Task 8 below).

- [ ] **Step 6: Commit**

```bash
git add resources/css/app.css vite.config.ts
git commit -m "feat: add marketing design tokens, marquee animation, Inter font"
```

---

## Phase 3: Reusable primitives

### Task 4: `useCountUp` composable

**Files:**
- Create: `resources/js/composables/useCountUp.ts`

- [ ] **Step 1: Implement**

```typescript
// resources/js/composables/useCountUp.ts
import { onMounted, onUnmounted, ref, type Ref } from 'vue';

interface UseCountUpOptions {
    target: number;
    duration?: number;
    decimals?: number;
}

/**
 * Animates a number from 0 to `target` once the returned `elementRef` enters
 * the viewport. Respects prefers-reduced-motion by jumping straight to the
 * target value.
 */
export function useCountUp({ target, duration = 1400, decimals = 0 }: UseCountUpOptions): {
    elementRef: Ref<HTMLElement | null>;
    value: Ref<number>;
} {
    const elementRef = ref<HTMLElement | null>(null);
    const value = ref(0);
    let observer: IntersectionObserver | null = null;

    const prefersReducedMotion = () =>
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const animate = () => {
        if (prefersReducedMotion()) {
            value.value = target;
            return;
        }

        const start = performance.now();

        const step = (now: number) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            value.value = Number((target * eased).toFixed(decimals));

            if (progress < 1) {
                requestAnimationFrame(step);
            }
        };

        requestAnimationFrame(step);
    };

    onMounted(() => {
        if (!elementRef.value) {
            return;
        }

        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        animate();
                        observer?.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.4 },
        );

        observer.observe(elementRef.value);
    });

    onUnmounted(() => observer?.disconnect());

    return { elementRef, value };
}
```

- [ ] **Step 2: Verify**

Run: `npm run types:check` — expect no errors.

- [ ] **Step 3: Commit**

```bash
git add resources/js/composables/useCountUp.ts
git commit -m "feat: add useCountUp composable for animated statistics"
```

### Task 5: `ProductScreenshotFrame.vue`

**Files:**
- Create: `resources/js/components/marketing/ProductScreenshotFrame.vue`

This is the literal "make space for screenshots" primitive: a styled browser-chrome frame at the right aspect ratio. Pass `src` once a real screenshot exists; until then it renders a clearly-labelled placeholder — never a fake screenshot.

- [ ] **Step 1: Implement**

```vue
<script setup lang="ts">
import { ImageIcon } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        src?: string;
        alt: string;
        label: string;
        aspect?: string;
    }>(),
    { aspect: 'aspect-[16/10]' },
);
</script>

<template>
    <div
        class="overflow-hidden rounded-2xl border border-marketing-border bg-white shadow-[0_20px_60px_-25px_rgba(33,13,2,0.35)]"
    >
        <div class="flex items-center gap-1.5 border-b border-marketing-border bg-marketing-eggshell/60 px-4 py-2.5">
            <span class="size-2.5 rounded-full bg-marketing-orange/50" />
            <span class="size-2.5 rounded-full bg-marketing-amber/60" />
            <span class="size-2.5 rounded-full bg-marketing-olive/40" />
        </div>
        <div :class="['relative w-full', aspect]">
            <img
                v-if="src"
                :src="src"
                :alt="alt"
                loading="lazy"
                decoding="async"
                class="absolute inset-0 h-full w-full object-cover object-top"
            />
            <div
                v-else
                class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-marketing-eggshell to-marketing-cream text-marketing-ink-muted"
            >
                <ImageIcon class="size-8" aria-hidden="true" />
                <p class="text-sm font-medium">{{ label }}</p>
                <p class="text-xs">Screenshot coming soon</p>
            </div>
        </div>
    </div>
</template>
```

- [ ] **Step 2: Verify**

Run: `npm run types:check`. Manually render it once (Task 12) with no `src` and confirm the placeholder state looks intentional, not broken.

- [ ] **Step 3: Commit**

```bash
git add resources/js/components/marketing/ProductScreenshotFrame.vue
git commit -m "feat: add ProductScreenshotFrame placeholder component"
```

### Task 6: `FeatureShowcase.vue` (alternating text/visual panel)

**Files:**
- Create: `resources/js/components/marketing/FeatureShowcase.vue`

Reusable version of Lynai's "Real time workload tracking & insights" panel: eyebrow label, heading, body copy, optional bullet list, and a visual slot (fed `AnimatedStatPanel`, `AnimatedBarChart`, or `ProductScreenshotFrame` by the caller), alternating left/right via a `reverse` prop, on a soft gradient background.

- [ ] **Step 1: Implement**

```vue
<script setup lang="ts">
withDefaults(
    defineProps<{
        eyebrow: string;
        title: string;
        body: string;
        bullets?: string[];
        reverse?: boolean;
    }>(),
    { bullets: () => [], reverse: false },
);
</script>

<template>
    <div
        class="grid items-center gap-12 rounded-3xl border border-marketing-border bg-gradient-to-br from-marketing-lavender/40 via-marketing-orange/10 to-marketing-cream p-8 sm:p-12 lg:grid-cols-2 lg:p-16"
    >
        <div :class="reverse ? 'lg:order-2' : 'lg:order-1'">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-marketing-orange">
                {{ eyebrow }}
            </p>
            <h3 class="mt-3 text-3xl font-semibold leading-tight tracking-tight text-marketing-ink sm:text-4xl">
                {{ title }}
            </h3>
            <p class="mt-4 max-w-md text-base leading-relaxed text-marketing-ink-muted">
                {{ body }}
            </p>
            <ul v-if="bullets.length" class="mt-6 space-y-3">
                <li
                    v-for="bullet in bullets"
                    :key="bullet"
                    class="flex items-center gap-3 text-sm font-medium text-marketing-ink"
                >
                    <span class="flex size-5 items-center justify-center rounded-full bg-marketing-orange/15 text-marketing-orange">
                        ✓
                    </span>
                    {{ bullet }}
                </li>
            </ul>
        </div>
        <div :class="reverse ? 'lg:order-1' : 'lg:order-2'">
            <slot name="visual" />
        </div>
    </div>
</template>
```

- [ ] **Step 2: Verify** — `npm run types:check`.
- [ ] **Step 3: Commit**

```bash
git add resources/js/components/marketing/FeatureShowcase.vue
git commit -m "feat: add FeatureShowcase alternating panel component"
```

### Task 7: `AnimatedStatPanel.vue` and `AnimatedBarChart.vue`

**Files:**
- Create: `resources/js/components/marketing/AnimatedStatPanel.vue`
- Create: `resources/js/components/marketing/AnimatedBarChart.vue`

Small, honest data-viz widgets (not screenshots) mirroring Lynai's "Workload Analytics" card and "Human vs AI Performance" bar comparison — built from `useCountUp`, no chart library needed.

- [ ] **Step 1: Implement `AnimatedStatPanel.vue`**

```vue
<script setup lang="ts">
import { useCountUp } from '@/composables/useCountUp';

const props = defineProps<{
    title: string;
    percent: number;
    percentLabel: string;
    rows: { label: string; percent: number; color: string }[];
}>();

const { elementRef, value } = useCountUp({ target: props.percent, decimals: 0 });
</script>

<template>
    <div ref="elementRef" class="rounded-2xl border border-marketing-border bg-white p-6 shadow-[0_20px_60px_-25px_rgba(33,13,2,0.35)]">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-marketing-ink-muted">
            {{ title }}
        </p>
        <p class="mt-2 flex items-baseline gap-1 text-4xl font-semibold text-marketing-ink">
            {{ Math.round(value) }}<span class="text-2xl">%</span>
        </p>
        <p class="text-xs text-marketing-ink-muted">{{ percentLabel }}</p>

        <ul class="mt-5 space-y-3">
            <li v-for="row in rows" :key="row.label" class="flex items-center justify-between text-sm">
                <span class="flex items-center gap-2 text-marketing-ink">
                    <span class="size-2 rounded-full" :style="{ backgroundColor: row.color }" />
                    {{ row.label }}
                </span>
                <span class="font-medium text-marketing-ink-muted">{{ row.percent }}%</span>
            </li>
        </ul>
    </div>
</template>
```

- [ ] **Step 2: Implement `AnimatedBarChart.vue`**

```vue
<script setup lang="ts">
import { onMounted, ref } from 'vue';

const props = defineProps<{
    title: string;
    bars: { label: string; percent: number; color: string; note?: string }[];
}>();

const containerRef = ref<HTMLElement | null>(null);
const revealed = ref(false);

onMounted(() => {
    if (!containerRef.value) {
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    revealed.value = true;
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.4 },
    );

    observer.observe(containerRef.value);
});
</script>

<template>
    <div ref="containerRef" class="rounded-2xl border border-marketing-border bg-white p-6 shadow-[0_20px_60px_-25px_rgba(33,13,2,0.35)]">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-marketing-ink-muted">
            {{ props.title }}
        </p>
        <div class="mt-5 space-y-4">
            <div v-for="bar in props.bars" :key="bar.label">
                <div class="mb-1.5 flex items-center justify-between text-sm">
                    <span class="font-medium text-marketing-ink">{{ bar.label }}</span>
                    <span v-if="bar.note" class="text-xs font-semibold text-marketing-orange">{{ bar.note }}</span>
                </div>
                <div class="h-2.5 w-full overflow-hidden rounded-full bg-marketing-eggshell">
                    <div
                        class="h-full rounded-full transition-[width] duration-[1200ms] ease-out"
                        :style="{ width: revealed ? `${bar.percent}%` : '0%', backgroundColor: bar.color }"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
```

- [ ] **Step 3: Verify** — `npm run types:check`.
- [ ] **Step 4: Commit**

```bash
git add resources/js/components/marketing/AnimatedStatPanel.vue resources/js/components/marketing/AnimatedBarChart.vue
git commit -m "feat: add animated stat panel and bar chart widgets"
```

---

## Phase 4: Page sections

### Task 8: `AnnouncementBar.vue` + `MarketingNav.vue`

**Files:**
- Create: `resources/js/components/marketing/AnnouncementBar.vue`
- Create: `resources/js/components/marketing/MarketingNav.vue`

- [ ] **Step 1: `AnnouncementBar.vue`**

```vue
<script setup lang="ts">
defineProps<{ text: string; href: string }>();
</script>

<template>
    <div class="bg-marketing-periwinkle px-4 py-2.5 text-center text-sm font-medium text-marketing-ink">
        <a :href="href" class="underline-offset-2 hover:underline">{{ text }}</a>
    </div>
</template>
```

- [ ] **Step 2: `MarketingNav.vue`** — sticky, blurred-on-scroll header with in-page anchors + real auth links.

```vue
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Menu, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { login } from '@/routes';

const links = [
    { name: 'Product', href: '#product' },
    { name: 'Pricing', href: '#pricing' },
    { name: 'FAQ', href: '#faq' },
];

const mobileOpen = ref(false);
</script>

<template>
    <header class="sticky top-0 z-40 border-b border-marketing-border bg-marketing-cream/85 backdrop-blur">
        <div class="mx-auto flex h-18 max-w-7xl items-center justify-between gap-6 px-4 py-3 sm:px-6 lg:px-8">
            <a href="#top" class="flex items-center gap-2 text-lg font-semibold tracking-tight text-marketing-ink">
                <span class="flex size-8 items-center justify-center rounded-lg bg-marketing-ink text-sm font-bold text-marketing-amber">
                    C
                </span>
                CourierOS
            </a>

            <nav class="hidden items-center gap-1 md:flex">
                <a
                    v-for="link in links"
                    :key="link.name"
                    :href="link.href"
                    class="rounded-md px-3 py-2 text-sm font-medium text-marketing-ink/80 transition hover:bg-marketing-eggshell hover:text-marketing-ink"
                >
                    {{ link.name }}
                </a>
            </nav>

            <div class="hidden items-center gap-2 md:flex">
                <Link :href="login()" class="rounded-md px-3 py-2 text-sm font-medium text-marketing-ink/80 hover:text-marketing-ink">
                    Login
                </Link>
                <a
                    href="/signup"
                    class="rounded-lg bg-marketing-amber px-5 py-2.5 text-sm font-semibold text-marketing-ink transition hover:brightness-95"
                >
                    Get Started
                </a>
            </div>

            <button
                type="button"
                class="inline-flex size-9 items-center justify-center rounded-md border border-marketing-border md:hidden"
                aria-label="Toggle menu"
                @click="mobileOpen = !mobileOpen"
            >
                <Menu v-if="!mobileOpen" class="size-5" />
                <X v-else class="size-5" />
            </button>
        </div>

        <div v-if="mobileOpen" class="border-t border-marketing-border md:hidden">
            <div class="mx-auto max-w-7xl space-y-1 px-4 py-3">
                <a
                    v-for="link in links"
                    :key="link.name"
                    :href="link.href"
                    class="block rounded-md px-3 py-2 text-sm font-medium text-marketing-ink/80"
                    @click="mobileOpen = false"
                >
                    {{ link.name }}
                </a>
                <div class="flex gap-2 pt-2">
                    <Link :href="login()" class="flex-1 rounded-md border border-marketing-border px-3 py-2 text-center text-sm font-medium">
                        Login
                    </Link>
                    <a href="/signup" class="flex-1 rounded-md bg-marketing-amber px-3 py-2 text-center text-sm font-semibold text-marketing-ink">
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </header>
</template>
```

- [ ] **Step 3: Verify** — `npm run types:check`.
- [ ] **Step 4: Commit**

```bash
git add resources/js/components/marketing/AnnouncementBar.vue resources/js/components/marketing/MarketingNav.vue
git commit -m "feat: add marketing announcement bar and sticky nav"
```

### Task 9: `HeroSection.vue`

**Files:**
- Create: `resources/js/components/marketing/HeroSection.vue`

Headline + subtext + two CTAs + rotating pill tabs (Admin Dashboard / Customer Portal) driving a `ProductScreenshotFrame`.

- [ ] **Step 1: Implement**

```vue
<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import ProductScreenshotFrame from './ProductScreenshotFrame.vue';

const tabs = [
    { key: 'admin', label: 'Admin Dashboard', screenshotLabel: 'Admin dashboard screenshot' },
    { key: 'portal', label: 'Customer Portal', screenshotLabel: 'Customer portal screenshot' },
];

const active = ref(0);
let timer: ReturnType<typeof setInterval> | undefined;

onMounted(() => {
    timer = setInterval(() => {
        active.value = (active.value + 1) % tabs.length;
    }, 4500);
});

onUnmounted(() => clearInterval(timer));
</script>

<template>
    <section id="top" class="relative overflow-hidden bg-marketing-cream fade-in-section">
        <div class="mx-auto max-w-5xl px-4 pt-20 pb-16 text-center sm:px-6 lg:px-8">
            <span
                class="inline-flex items-center gap-2 rounded-full border border-marketing-border bg-white px-4 py-1.5 text-xs font-medium text-marketing-ink shadow-sm"
            >
                <span class="rounded-full bg-marketing-orange px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">
                    New
                </span>
                Now onboarding Caribbean courier businesses
            </span>

            <h1 class="mx-auto mt-6 max-w-3xl text-balance text-5xl font-semibold leading-[1.05] tracking-tight text-marketing-ink sm:text-6xl">
                Run Your Courier Business Like a Platform
            </h1>

            <p class="mx-auto mt-5 max-w-xl text-lg text-marketing-ink-muted">
                Pre-alerts, package tracking, billing, and customer updates — on your own branded site.
                Launch in minutes, not months.
            </p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a
                    href="#product"
                    class="rounded-lg border border-marketing-border bg-white px-6 py-3 text-sm font-semibold text-marketing-ink transition hover:bg-marketing-eggshell"
                >
                    See How It Works
                </a>
                <a
                    href="/signup"
                    class="inline-flex items-center gap-2 rounded-lg bg-marketing-amber px-6 py-3 text-sm font-semibold text-marketing-ink transition hover:brightness-95"
                >
                    Start Free Trial →
                </a>
            </div>
        </div>

        <div class="mx-auto max-w-5xl px-4 pb-20 sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-center gap-2">
                <button
                    v-for="(tab, index) in tabs"
                    :key="tab.key"
                    type="button"
                    class="rounded-full px-4 py-2 text-sm font-medium transition"
                    :class="
                        active === index
                            ? 'bg-marketing-periwinkle text-marketing-ink'
                            : 'text-marketing-ink-muted hover:bg-marketing-eggshell'
                    "
                    @click="active = index"
                >
                    {{ tab.label }}
                </button>
            </div>
            <ProductScreenshotFrame
                :alt="tabs[active].label"
                :label="tabs[active].screenshotLabel"
                aspect="aspect-[16/9]"
            />
        </div>
    </section>
</template>
```

- [ ] **Step 2: Verify** — `npm run types:check`. Visually confirm the auto-rotating tabs match Lynai's cadence (~4–5s) and are also clickable.
- [ ] **Step 3: Commit**

```bash
git add resources/js/components/marketing/HeroSection.vue
git commit -m "feat: add marketing hero section with rotating product tabs"
```

### Task 10: `LogoMarquee.vue`

**Files:**
- Create: `resources/js/components/marketing/LogoMarquee.vue`

Data-driven, hidden by default (see the "no fabricated social proof" ground rule above).

- [ ] **Step 1: Implement**

```vue
<script setup lang="ts">
withDefaults(defineProps<{ logos?: { name: string; href?: string }[] }>(), { logos: () => [] });
</script>

<template>
    <section v-if="logos.length" class="border-y border-marketing-border bg-white py-10 fade-in-section">
        <p class="mb-6 text-center text-xs font-semibold uppercase tracking-[0.2em] text-marketing-ink-muted">
            Trusted by courier businesses across the Caribbean
        </p>
        <div class="overflow-hidden">
            <div class="marketing-marquee-track flex w-max items-center gap-16">
                <span
                    v-for="logo in [...logos, ...logos]"
                    :key="`${logo.name}-${Math.random()}`"
                    class="text-xl font-semibold text-marketing-ink-muted grayscale transition hover:text-marketing-ink hover:grayscale-0"
                >
                    {{ logo.name }}
                </span>
            </div>
        </div>
    </section>
</template>
```

- [ ] **Step 2: Verify** — `npm run types:check`. Render with an empty `logos` prop (default) and confirm the section is absent (no empty whitespace band).
- [ ] **Step 3: Commit**

```bash
git add resources/js/components/marketing/LogoMarquee.vue
git commit -m "feat: add opt-in logo marquee (hidden until real logos exist)"
```

### Task 11: `TestimonialSwitcher.vue`, `IntegrationsGrid.vue`, `StatsBanner.vue`, `PricingSection.vue`, `FeatureGrid.vue`, `FaqAccordion.vue`, `FinalCta.vue`, `MarketingFooter.vue`

**Files:**
- Create: `resources/js/components/marketing/TestimonialSwitcher.vue`
- Create: `resources/js/components/marketing/IntegrationsGrid.vue`
- Create: `resources/js/components/marketing/StatsBanner.vue`
- Create: `resources/js/components/marketing/PricingSection.vue`
- Create: `resources/js/components/marketing/FeatureGrid.vue`
- Create: `resources/js/components/marketing/FaqAccordion.vue`
- Create: `resources/js/components/marketing/FinalCta.vue`
- Create: `resources/js/components/marketing/MarketingFooter.vue`

This task bundles the remaining sections since each is a straightforward, self-contained presentational component. Implement and commit them one at a time (still one commit per file) rather than as a single mega-commit — the step numbering below is per-component.

- [ ] **Step 1: `TestimonialSwitcher.vue`** — data-driven, empty by default (ground rule above).

```vue
<script setup lang="ts">
import { ref } from 'vue';

interface Testimonial {
    quote: string;
    name: string;
    company: string;
}

const props = withDefaults(defineProps<{ testimonials?: Testimonial[] }>(), { testimonials: () => [] });
const active = ref(0);
</script>

<template>
    <section v-if="props.testimonials.length" class="bg-white py-20 fade-in-section">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <p class="text-2xl font-medium leading-relaxed tracking-tight text-marketing-ink sm:text-3xl">
                "{{ props.testimonials[active].quote }}"
            </p>
            <p class="mt-4 text-sm font-semibold text-marketing-ink-muted">
                {{ props.testimonials[active].name }} · {{ props.testimonials[active].company }}
            </p>
            <div v-if="props.testimonials.length > 1" class="mt-6 flex justify-center gap-2">
                <button
                    v-for="(t, index) in props.testimonials"
                    :key="t.company"
                    type="button"
                    class="size-2 rounded-full transition"
                    :class="active === index ? 'bg-marketing-orange' : 'bg-marketing-eggshell'"
                    :aria-label="`Show testimonial from ${t.company}`"
                    @click="active = index"
                />
            </div>
        </div>
    </section>
</template>
```

- [ ] **Step 2: `IntegrationsGrid.vue`**

```vue
<script setup lang="ts">
const integrations = [
    'Stripe', 'WhatsApp Business', 'Resend', 'QuickBooks', 'Zapier', 'Google Sheets', 'Slack', 'Mailchimp',
];
</script>

<template>
    <section id="integrations" class="bg-marketing-eggshell/40 py-20 fade-in-section">
        <div class="mx-auto max-w-6xl px-4 text-center sm:px-6 lg:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-marketing-orange">Integrations</p>
            <h2 class="mt-2 text-balance text-3xl font-semibold tracking-tight text-marketing-ink sm:text-4xl">
                Works with the tools you already use
            </h2>
            <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div
                    v-for="name in integrations"
                    :key="name"
                    class="rounded-xl border border-marketing-border bg-white px-4 py-6 text-sm font-medium text-marketing-ink shadow-sm transition hover:-translate-y-1 hover:shadow-md fade-in-item"
                >
                    {{ name }}
                </div>
            </div>
        </div>
    </section>
</template>
```

Note: this lists integrations CourierOS is *built to support* (Stripe billing and WhatsApp notifications are real, already in `app/Channels/WhatsApp` and `config/cashier.php`; the rest are illustrative "coming soon" targets). If that distinction matters, split the array into `live` vs `planned` and badge them differently — flagging this as a content decision for plan review rather than guessing.

- [ ] **Step 3: `StatsBanner.vue`** — the large dark section with true-by-design capability stats and one big screenshot placeholder.

```vue
<script setup lang="ts">
import { useCountUp } from '@/composables/useCountUp';
import ProductScreenshotFrame from './ProductScreenshotFrame.vue';

const stats = [
    { prefix: '$', target: 79, suffix: '/mo', label: 'Starting price, no hidden fees' },
    { prefix: '', target: 24, suffix: '/7', label: 'Automated tracking & WhatsApp updates' },
    { prefix: '', target: 100, suffix: '%', label: 'Tenant data isolated by design' },
    { prefix: '<', target: 1, suffix: ' day', label: 'To launch your branded site' },
];
</script>

<template>
    <section class="bg-marketing-ink py-20 text-marketing-cream fade-in-section">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-marketing-orange">
                    Real-time intelligence &amp; control
                </p>
                <h2 class="mt-2 text-balance text-3xl font-semibold tracking-tight sm:text-4xl">
                    See every package, pre-alert, and payment the moment it happens
                </h2>
            </div>

            <div class="mt-12">
                <ProductScreenshotFrame
                    alt="Customer portal screenshot"
                    label="Customer portal screenshot"
                    aspect="aspect-[16/8]"
                />
            </div>

            <dl class="mt-14 grid grid-cols-2 gap-8 sm:grid-cols-4">
                <div v-for="stat in stats" :key="stat.label" class="text-center">
                    <dt class="sr-only">{{ stat.label }}</dt>
                    <dd class="text-3xl font-semibold sm:text-4xl">
                        {{ stat.prefix }}{{ stat.target }}{{ stat.suffix }}
                    </dd>
                    <p class="mt-2 text-sm text-marketing-cream/70">{{ stat.label }}</p>
                </div>
            </dl>
        </div>
    </section>
</template>
```

(Kept these as static numerals rather than wiring `useCountUp` per-stat for the `$79/mo` and `<1 day` cases, since those aren't pure integers counting up meaningfully — only genuinely numeric stats should animate. If you want all four to count up, drop the `$`/`/mo`/`<`/`day` formatting and use `useCountUp` directly as shown in `AnimatedStatPanel.vue`.)

- [ ] **Step 4: `PricingSection.vue`** — real numbers from `TenantSignupController`, two cards (Starter, Enterprise), no invented tiers, no fake monthly/yearly toggle (CourierOS has no annual plan today).

```vue
<script setup lang="ts">
defineProps<{ pricing: { monthly: number; setup: number; currency: string } }>();

const starterFeatures = [
    'Your own branded subdomain',
    'Pre-alerts & package tracking',
    'Customer self-serve portal',
    'Stripe-powered billing',
    'Email & WhatsApp notifications',
    'Weight-based rate calculator',
];

const enterpriseFeatures = [
    'Everything in Starter',
    'Multiple warehouse locations',
    'Custom domain & integrations',
    'Dedicated onboarding support',
];
</script>

<template>
    <section id="pricing" class="bg-white py-20 fade-in-section">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-marketing-orange">Pricing</p>
                <h2 class="mt-2 text-balance text-3xl font-semibold tracking-tight text-marketing-ink sm:text-4xl">
                    Choose the Right Plan
                </h2>
            </div>

            <div class="mt-12 grid gap-8 sm:grid-cols-2">
                <div class="rounded-2xl border-2 border-marketing-amber bg-marketing-cream p-8 shadow-[0_20px_60px_-25px_rgba(33,13,2,0.35)]">
                    <p class="text-sm font-semibold text-marketing-ink-muted">Starter</p>
                    <p class="mt-2 flex items-baseline gap-1 text-marketing-ink">
                        <span class="text-4xl font-semibold">${{ pricing.monthly }}</span>
                        <span class="text-sm text-marketing-ink-muted">/month</span>
                    </p>
                    <p class="mt-1 text-xs text-marketing-ink-muted">
                        + ${{ pricing.setup }} one-time setup · {{ pricing.currency }} · cancel anytime
                    </p>
                    <a
                        href="/signup"
                        class="mt-6 block rounded-lg bg-marketing-amber px-6 py-3 text-center text-sm font-semibold text-marketing-ink transition hover:brightness-95"
                    >
                        Start Free Trial
                    </a>
                    <ul class="mt-6 space-y-3 text-sm text-marketing-ink">
                        <li v-for="f in starterFeatures" :key="f" class="flex items-center gap-2">
                            <span class="text-marketing-orange">✓</span> {{ f }}
                        </li>
                    </ul>
                </div>

                <div class="rounded-2xl border border-marketing-border bg-marketing-eggshell/40 p-8">
                    <p class="text-sm font-semibold text-marketing-ink-muted">Enterprise</p>
                    <p class="mt-2 text-4xl font-semibold text-marketing-ink">Custom</p>
                    <p class="mt-1 text-xs text-marketing-ink-muted">For larger, multi-location operations</p>
                    <a
                        href="mailto:hello@courieros.co"
                        class="mt-6 block rounded-lg border border-marketing-ink px-6 py-3 text-center text-sm font-semibold text-marketing-ink transition hover:bg-marketing-ink hover:text-marketing-cream"
                    >
                        Contact Sales
                    </a>
                    <ul class="mt-6 space-y-3 text-sm text-marketing-ink">
                        <li v-for="f in enterpriseFeatures" :key="f" class="flex items-center gap-2">
                            <span class="text-marketing-orange">✓</span> {{ f }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</template>
```

`mailto:hello@courieros.co` is a placeholder inbox — confirm the real sales contact address before shipping (there's no central-domain contact route in this codebase today; the existing `contact()` route belongs to the tenant public site).

- [ ] **Step 5: `FeatureGrid.vue`**

```vue
<script setup lang="ts">
import { BadgeCheck, Bell, Calculator, LayoutDashboard, MapPinned, Package, Receipt, ShieldCheck, Users } from 'lucide-vue-next';

const features = [
    { icon: Receipt, title: 'Pre-Alert Management', body: 'Customers submit invoice and shipment details before packages ever arrive.' },
    { icon: Package, title: 'Package Tracking', body: 'A clear status timeline from warehouse intake through pickup.' },
    { icon: MapPinned, title: 'Branded Subdomain', body: 'Your business live at yourname.courieros.co in minutes — a custom domain later.' },
    { icon: BadgeCheck, title: 'Stripe Billing', body: 'Your platform subscription is billed automatically and securely.' },
    { icon: Bell, title: 'WhatsApp Notifications', body: 'Status updates sent by email and WhatsApp, where customers already are.' },
    { icon: Calculator, title: 'Rate Calculator', body: 'Transparent, weight-based pricing your customers can check themselves.' },
    { icon: LayoutDashboard, title: 'Customer Portal', body: 'A self-serve dashboard for every customer — no phone calls needed.' },
    { icon: Users, title: 'Role-Based Staff Access', body: 'Owner, admin, and staff permissions are built in from day one.' },
    { icon: ShieldCheck, title: 'Reports & Analytics', body: 'See packages, revenue, and activity at a glance.' },
];
</script>

<template>
    <section id="product" class="bg-marketing-cream py-20 fade-in-section">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-marketing-orange">Extra Features</p>
                <h2 class="mt-2 text-balance text-3xl font-semibold tracking-tight text-marketing-ink sm:text-4xl">
                    Discover Endless Opportunities
                </h2>
            </div>
            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="(feature, index) in features"
                    :key="feature.title"
                    class="rounded-2xl border border-marketing-border bg-white p-6 fade-in-item transition hover:-translate-y-1 hover:shadow-lg"
                    :style="{ '--fade-delay': `${index * 60}ms` }"
                >
                    <div class="flex size-10 items-center justify-center rounded-lg bg-marketing-orange/12 text-marketing-orange">
                        <component :is="feature.icon" class="size-5" />
                    </div>
                    <h3 class="mt-4 font-semibold text-marketing-ink">{{ feature.title }}</h3>
                    <p class="mt-1.5 text-sm text-marketing-ink-muted">{{ feature.body }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
```

- [ ] **Step 6: `FaqAccordion.vue`** — built on the existing `Collapsible` primitive (`resources/js/components/ui/collapsible`), matching the project's existing shadcn-vue/reka-ui usage instead of introducing a new accordion dependency.

```vue
<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';
import { ref } from 'vue';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';

const faqs = [
    { q: 'What is CourierOS?', a: 'CourierOS is a platform for Caribbean courier and freight-forwarding businesses to run pre-alerts, package tracking, billing, and customer communication — all from one branded site.' },
    { q: 'How fast can I launch?', a: 'Sign up, choose a subdomain, and your branded site is live in minutes. You can connect a custom domain later.' },
    { q: 'Do I need technical skills?', a: 'No. Branding, shipping rates, and your warehouse address are all configured from your admin dashboard — no code required.' },
    { q: 'How does billing work?', a: `CourierOS is $79/month plus a one-time $349 setup fee, billed securely through Stripe. Cancel anytime.` },
    { q: 'Can my customers get WhatsApp updates?', a: 'Yes — status notifications can be sent by email and WhatsApp.' },
    { q: 'Is my data separated from other courier businesses?', a: 'Yes. Every courier business on CourierOS has fully isolated data by design.' },
];

const openIndex = ref<number | null>(0);
</script>

<template>
    <section id="faq" class="bg-marketing-eggshell/40 py-20 fade-in-section">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-marketing-orange">FAQ</p>
                <h2 class="mt-2 text-balance text-3xl font-semibold tracking-tight text-marketing-ink sm:text-4xl">
                    Frequently Asked Questions
                </h2>
            </div>

            <div class="mt-10 space-y-3">
                <Collapsible
                    v-for="(faq, index) in faqs"
                    :key="faq.q"
                    :open="openIndex === index"
                    class="rounded-xl border border-marketing-border bg-white"
                    @update:open="(open) => (openIndex = open ? index : null)"
                >
                    <CollapsibleTrigger class="flex w-full items-center justify-between gap-4 px-6 py-4 text-left text-base font-semibold text-marketing-ink">
                        {{ faq.q }}
                        <ChevronDown
                            class="size-4 shrink-0 text-marketing-ink-muted transition-transform"
                            :class="{ 'rotate-180': openIndex === index }"
                        />
                    </CollapsibleTrigger>
                    <CollapsibleContent class="px-6 pb-4 text-sm leading-relaxed text-marketing-ink-muted">
                        {{ faq.a }}
                    </CollapsibleContent>
                </Collapsible>
            </div>
        </div>
    </section>
</template>
```

Verify the `Collapsible`/`CollapsibleTrigger`/`CollapsibleContent` export names against `resources/js/components/ui/collapsible/index.ts` before wiring this up — match whatever the existing component actually exports (used elsewhere in the app, e.g. the sidebar); adjust prop names (`open`/`@update:open` vs `v-model:open`) to whatever reka-ui's `Collapsible` actually expects.

- [ ] **Step 7: `FinalCta.vue`**

```vue
<script setup lang="ts">
</script>

<template>
    <section class="relative overflow-hidden bg-marketing-ink py-20 text-marketing-cream fade-in-section">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_30%,rgba(250,143,31,0.18),transparent_50%),radial-gradient(circle_at_80%_60%,rgba(252,213,25,0.12),transparent_45%)]" aria-hidden="true" />
        <div class="relative mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-balance text-3xl font-semibold tracking-tight sm:text-4xl">
                Launch your courier platform today
            </h2>
            <p class="mx-auto mt-4 max-w-xl text-base text-marketing-cream/70">
                Create your branded courier site in minutes. Payment details are handled securely by Stripe — we never see your card.
            </p>
            <a
                href="/signup"
                class="mt-8 inline-flex items-center gap-2 rounded-lg bg-marketing-amber px-8 py-3.5 text-sm font-semibold text-marketing-ink transition hover:brightness-95"
            >
                Create Free Account →
            </a>
        </div>
    </section>
</template>
```

- [ ] **Step 8: `MarketingFooter.vue`**

```vue
<script setup lang="ts">
const pages = [
    { name: 'Product', href: '#product' },
    { name: 'Pricing', href: '#pricing' },
    { name: 'FAQ', href: '#faq' },
];

const utility = [
    { name: 'Privacy Policy', href: '/legal/privacy' },
    { name: 'Terms & Conditions', href: '/legal/terms' },
];
</script>

<template>
    <footer class="border-t border-marketing-border bg-marketing-cream">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-4 lg:px-8">
            <div class="space-y-3">
                <p class="flex items-center gap-2 text-lg font-semibold tracking-tight text-marketing-ink">
                    <span class="flex size-7 items-center justify-center rounded-md bg-marketing-ink text-xs font-bold text-marketing-amber">C</span>
                    CourierOS
                </p>
                <p class="text-sm text-marketing-ink-muted">
                    The platform Caribbean courier businesses run on.
                </p>
            </div>
            <div>
                <p class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-marketing-ink-muted">Pages</p>
                <ul class="space-y-2 text-sm">
                    <li v-for="link in pages" :key="link.name">
                        <a :href="link.href" class="text-marketing-ink-muted transition hover:text-marketing-ink">{{ link.name }}</a>
                    </li>
                </ul>
            </div>
            <div>
                <p class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-marketing-ink-muted">Utility</p>
                <ul class="space-y-2 text-sm">
                    <li v-for="link in utility" :key="link.name">
                        <a :href="link.href" class="text-marketing-ink-muted transition hover:text-marketing-ink">{{ link.name }}</a>
                    </li>
                </ul>
            </div>
            <div>
                <p class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-marketing-ink-muted">Get started</p>
                <a href="/signup" class="inline-block rounded-lg bg-marketing-amber px-5 py-2.5 text-sm font-semibold text-marketing-ink">
                    Start Free Trial
                </a>
            </div>
        </div>
        <div class="border-t border-marketing-border">
            <p class="mx-auto max-w-6xl px-4 py-4 text-xs text-marketing-ink-muted sm:px-6 lg:px-8">
                © {{ new Date().getFullYear() }} CourierOS. All rights reserved.
            </p>
        </div>
    </footer>
</template>
```

`utility` links reuse the tenant public site's legal routes (`/legal/privacy`, `/legal/terms`) since there's no separate central-domain legal content yet — flag this as a content gap for follow-up if CourierOS needs its own ToS distinct from a tenant's shipping ToS.

- [ ] **Step 9: Verify all eight** — `npm run types:check && npm run lint:check`.
- [ ] **Step 10: Commit** (one commit is fine here since these were built together)

```bash
git add resources/js/components/marketing/TestimonialSwitcher.vue resources/js/components/marketing/IntegrationsGrid.vue resources/js/components/marketing/StatsBanner.vue resources/js/components/marketing/PricingSection.vue resources/js/components/marketing/FeatureGrid.vue resources/js/components/marketing/FaqAccordion.vue resources/js/components/marketing/FinalCta.vue resources/js/components/marketing/MarketingFooter.vue
git commit -m "feat: add remaining marketing sections (testimonial, integrations, stats, pricing, features, faq, final CTA, footer)"
```

---

## Phase 5: Assembly

### Task 12: `central/Home.vue` — assemble the page

**Files:**
- Create: `resources/js/pages/central/Home.vue`

- [ ] **Step 1: Implement**

```vue
<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AnimatedBarChart from '@/components/marketing/AnimatedBarChart.vue';
import AnimatedStatPanel from '@/components/marketing/AnimatedStatPanel.vue';
import AnnouncementBar from '@/components/marketing/AnnouncementBar.vue';
import FaqAccordion from '@/components/marketing/FaqAccordion.vue';
import FeatureGrid from '@/components/marketing/FeatureGrid.vue';
import FeatureShowcase from '@/components/marketing/FeatureShowcase.vue';
import FinalCta from '@/components/marketing/FinalCta.vue';
import HeroSection from '@/components/marketing/HeroSection.vue';
import IntegrationsGrid from '@/components/marketing/IntegrationsGrid.vue';
import LogoMarquee from '@/components/marketing/LogoMarquee.vue';
import MarketingFooter from '@/components/marketing/MarketingFooter.vue';
import MarketingNav from '@/components/marketing/MarketingNav.vue';
import PricingSection from '@/components/marketing/PricingSection.vue';
import StatsBanner from '@/components/marketing/StatsBanner.vue';
import TestimonialSwitcher from '@/components/marketing/TestimonialSwitcher.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';

defineOptions({ layout: null });

defineProps<{
    pricing: { monthly: number; setup: number; currency: string };
}>();

useScrollReveal();
</script>

<template>
    <Head title="CourierOS — Run Your Courier Business Like a Platform" />

    <div class="font-marketing bg-marketing-cream text-marketing-ink">
        <AnnouncementBar
            text="Now onboarding Caribbean courier businesses — start free"
            href="/signup"
        />
        <MarketingNav />

        <HeroSection />

        <LogoMarquee :logos="[]" />

        <TestimonialSwitcher :testimonials="[]" />

        <IntegrationsGrid />

        <div class="mx-auto max-w-6xl space-y-8 px-4 py-4 sm:px-6 lg:px-8">
            <FeatureShowcase
                eyebrow="What CourierOS can do for you"
                title="Real-time package &amp; pre-alert tracking"
                body="Every pre-alert and package status update lands in one dashboard, so you always know what's moving through your warehouse."
                :bullets="['Live status timeline', 'Instant pre-alert matching']"
            >
                <template #visual>
                    <AnimatedStatPanel
                        title="Package Status Overview"
                        :percent="72"
                        percent-label="of packages currently in transit"
                        :rows="[
                            { label: 'Pre-Alerted', percent: 88, color: '#FA8F1F' },
                            { label: 'At Warehouse', percent: 64, color: '#FCD519' },
                            { label: 'Ready for Pickup', percent: 41, color: '#AA8322' },
                        ]"
                    />
                </template>
            </FeatureShowcase>

            <FeatureShowcase
                reverse
                eyebrow="Grow with confidence"
                title="Clear performance metrics for your business"
                body="Track packages processed and revenue trends so you know your business is growing — not just guessing."
                :bullets="['Monthly package volume', 'Revenue trend at a glance']"
            >
                <template #visual>
                    <AnimatedBarChart
                        title="Packages Processed"
                        :bars="[
                            { label: 'Jan', percent: 40, color: '#FA8F1F' },
                            { label: 'Feb', percent: 55, color: '#FA8F1F' },
                            { label: 'Mar', percent: 70, color: '#FA8F1F' },
                            { label: 'Apr', percent: 85, color: '#FCD519' },
                        ]"
                    />
                </template>
            </FeatureShowcase>

            <FeatureShowcase
                eyebrow="Ditch the spreadsheet"
                title="See how CourierOS compares to manual tracking"
                body="Spreadsheets and group chats don't scale. CourierOS replaces manual package tracking with a system built for it."
                :bullets="['No more lost pre-alerts', 'No more manual status updates']"
            >
                <template #visual>
                    <AnimatedBarChart
                        title="Time Spent on Package Admin"
                        :bars="[
                            { label: 'Spreadsheets & DMs', percent: 100, color: '#AA8322' },
                            { label: 'CourierOS', percent: 35, color: '#FA8F1F', note: '65% less' },
                        ]"
                    />
                </template>
            </FeatureShowcase>
        </div>

        <StatsBanner />

        <PricingSection :pricing="pricing" />

        <FeatureGrid />

        <FaqAccordion />

        <FinalCta />

        <MarketingFooter />
    </div>
</template>
```

- [ ] **Step 2: Run the backend test from Task 1 again**

Run: `php artisan test --filter=MarketingHomeTest -v`
Expected: PASS — the `central/Home` component now exists, so Inertia can resolve and render it.

- [ ] **Step 3: Full verification pass**

Run:
```bash
npm run types:check
npm run lint:check
npm run format:check
npm run build
php artisan test
```
Expected: all green.

- [ ] **Step 4: Manual visual pass**

Serve the app locally with the central domain (e.g. set `COURIEROS_CENTRAL_DOMAIN` to match your Herd host, or hit `http://127.0.0.1:8000/` via `php artisan serve` with `APP_URL` pointed at a non-tenant host) and compare side-by-side against the captured Lynai screenshots at 1440px and 390px widths:
- Sticky nav blurs/pins correctly.
- Hero tabs auto-rotate every ~4–5s and are clickable.
- Scroll-reveal fades sections in once per section (not re-triggering on scroll-up).
- FAQ accordion opens one at a time.
- Pricing shows $79/mo + $349 setup, not fabricated tiers.
- Logo marquee and testimonial sections are absent (empty arrays) — not broken-looking empty bands.
- `prefers-reduced-motion: reduce` (toggle in devtools) removes count-up/marquee motion without breaking layout.

- [ ] **Step 5: Commit**

```bash
git add resources/js/pages/central/Home.vue
git commit -m "feat: assemble the CourierOS marketing home page"
```

---

## Phase 6: Wrap-up

### Task 13: Update the manual smoke-test checklist

**Files:**
- Modify: `README.md` (the "Manual smoke-test checklist" section, around the "Public site (logged out)" heading)

- [ ] **Step 1:** Add a line documenting the new central-domain check, matching the existing checklist format:

```markdown
### Central marketing site (courieros.co, logged out)
- [ ] `/` on the central domain (not a tenant subdomain) renders the CourierOS marketing page — sticky nav, hero, pricing ($79/mo + $349 setup), FAQ accordion, footer.
- [ ] `/` on a tenant subdomain still renders that tenant's own public home page (regression check).
- [ ] `/signup` renders full-bleed with no dashboard sidebar around it.
```

- [ ] **Step 2: Commit**

```bash
git add README.md
git commit -m "docs: add central marketing site to the manual smoke-test checklist"
```

### Task 14: Final verification

- [ ] **Step 1:** Run the full backend suite: `php artisan test` — expect the pre-existing count plus the new `MarketingHomeTest` tests, all green.
- [ ] **Step 2:** Run `npm run lint:check && npm run format:check && npm run types:check && npm run build` — all green.
- [ ] **Step 3:** Walk the manual smoke-test checklist added in Task 13.
- [ ] **Step 4:** Review the diff for any leftover TODO/placeholder content that reads as a real claim rather than a placeholder (fake customer names, fake stats, wrong contact email) — this is the last honesty check before this is considered done.

---

## Explicitly out of scope (per plan review)

- Any backend/production-infrastructure work (Stripe live keys, WhatsApp provider credentials, deployment, DNS/domain cutover, email sending config) — you chose "landing page + frontend only."
- Redesigning the tenant-facing consumer site (`public/Home.vue`, `PublicLayout.vue`) — it keeps its current navy/red theme; TODAY Shipping is no longer the pitch vehicle but its code is untouched here.
- Building out the other Lynai template pages (Company, Blog, Case Studies, Career, Integration detail pages) — this plan builds a single one-page marketing site, matching "the landing page should reflect the product in its entirety" as one page, not a full multi-page site clone.
- Real product screenshots — `ProductScreenshotFrame` reserves the space; dropping in real PNGs/WebPs later is a one-line `src` prop change per usage site, no component changes needed.
