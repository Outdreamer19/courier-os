# CourierOS Multi-Tenant SaaS Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Convert the single-operator Ship'd JM courier platform into CourierOS — a multi-tenant SaaS where multiple Jamaican courier businesses each operate their own branded instance on a `{tenant}.courieros.co` subdomain, paying a monthly subscription plus setup fee via Stripe.

**Architecture:** Single shared database with `tenant_id` column scoping on all tenant-owned tables, enforced by a global Eloquent scope and a tenant-resolution middleware that identifies the tenant from the subdomain. A separate "central" domain (`courieros.co` / `app.courieros.co`) hosts marketing, tenant signup, and the platform-owner super-admin. Stripe Billing drives subscription lifecycle. Existing per-package InvoiceFeed billing is retained for tenant-to-customer invoicing; Stripe is added only for platform-to-tenant subscription billing.

**Tech Stack:** Laravel 13, Vue 3 + Inertia, TypeScript, Tailwind v4, Fortify auth, Resend email, Stripe (laravel/cashier), Meta WhatsApp Cloud API, Pest/PHPUnit, Forge + DigitalOcean + Cloudflare deploy.

---

## Key Architectural Decisions

1. **Tenancy model:** Single database, `tenant_id` foreign key on all tenant-scoped tables. A `BelongsToTenant` trait applies a global scope automatically. This minimizes ops burden for a solo founder and enables platform-wide analytics.

2. **Tenant resolution:** Middleware reads the subdomain. `{tenant}.courieros.co` resolves a `Tenant` record and binds it into the container + Inertia shared props. The central domain (`courieros.co` and `app.courieros.co`) bypasses tenant scoping and serves marketing + platform admin + tenant onboarding.

3. **Two billing systems, kept separate:**
   - **Platform billing (NEW):** CourierOS charges each tenant $79/mo + $350 setup via Stripe/Cashier. Lives on the central domain.
   - **Customer billing (EXISTING):** Each tenant charges *their* customers per package via InvoiceFeed. Unchanged except for tenant scoping.

4. **Per-tenant configuration:** currency (USD/JMD), customer reference prefix, warehouse address, shipping rates, branding (name, logo, colors), WhatsApp number — all move from global config into a `tenants` table or tenant-scoped tables.

5. **Branding:** The existing hardcoded "Ship'd JM" strings become tenant-driven. The central marketing site gets its own CourierOS brand.

---

## File Structure Overview

**New core tenancy:**
- `app/Models/Tenant.php` — the tenant (courier business) record
- `app/Models/Concerns/BelongsToTenant.php` — trait: global scope + auto-fill tenant_id
- `app/Http/Middleware/ResolveTenant.php` — subdomain → tenant binding
- `app/Support/Tenancy/TenantManager.php` — current-tenant container singleton
- `app/Providers/TenancyServiceProvider.php` — registers manager + scopes

**New platform (central domain) layer:**
- `app/Models/Subscription.php` (via Cashier), `app/Models/PlatformOwner` usage of User
- `app/Http/Controllers/Central/MarketingController.php`
- `app/Http/Controllers/Central/TenantSignupController.php`
- `app/Http/Controllers/Central/PlatformAdmin/*` — super-admin over all tenants
- `app/Http/Controllers/Central/BillingController.php` — Stripe checkout + portal

**New features:**
- `app/Services/WhatsApp/WhatsAppClient.php` + notification channel
- `app/Notifications/Channels/WhatsAppChannel.php`
- `app/Http/Controllers/Admin/ReportsController.php` + analytics services

**Modified:** all existing models (add `BelongsToTenant`), all migrations (add `tenant_id`), `HandleInertiaRequests`, `config/shipdjm.php` → `config/courieros.co`, route files, public Vue pages (branding), seeders.

---

## Phase 0: Foundation & Safety

### Task 0: Worktree, branch, baseline green

**Files:** none (setup)

- [ ] **Step 1:** Confirm working on a feature branch, never `main`. Create `git checkout -b feat/multitenancy`.
- [ ] **Step 2:** Run the full existing suite to establish a green baseline.
  Run: `php artisan test`
  Expected: all existing tests PASS. Record the count.
- [ ] **Step 3:** Run lint/format/types baseline.
  Run: `composer ci:check`
  Expected: PASS (or record pre-existing failures so we don't blame them on our work).
- [ ] **Step 4:** Commit a no-op marker if needed; otherwise proceed.

---

## Phase 1: Tenancy Core

### Task 1: Tenant model and migration

**Files:**
- Create: `database/migrations/2026_06_20_000001_create_tenants_table.php`
- Create: `app/Models/Tenant.php`
- Create: `database/factories/TenantFactory.php`
- Test: `tests/Feature/Tenancy/TenantModelTest.php`

- [ ] **Step 1: Write the failing test** — a tenant can be created with subdomain, name, currency, reference prefix, status; subdomain is unique.
- [ ] **Step 2:** Run test, expect FAIL (no table/model).
- [ ] **Step 3:** Write migration. Columns: `id`, `name`, `subdomain` (unique), `custom_domain` (nullable, unique), `status` (enum: pending, active, suspended, cancelled; default pending), `currency` (default USD), `customer_reference_prefix`, `package_reference_prefix` (default PKG), `whatsapp_number` (nullable), `logo_path` (nullable), `brand_primary_color` (nullable), `trial_ends_at` (nullable), `timestamps`. Write `Tenant` model with fillable, status constants, `active()` scope. Write factory.
- [ ] **Step 4:** Run test, expect PASS.
- [ ] **Step 5:** Commit.

### Task 2: BelongsToTenant trait and global scope

**Files:**
- Create: `app/Models/Concerns/BelongsToTenant.php`
- Create: `app/Support/Tenancy/TenantManager.php`
- Create: `app/Providers/TenancyServiceProvider.php`
- Modify: `bootstrap/providers.php`
- Test: `tests/Feature/Tenancy/TenantScopingTest.php`

- [ ] **Step 1: Write the failing test** — with tenant A bound as current, querying a model that uses the trait returns only tenant A rows; creating a model auto-fills `tenant_id`; with no tenant bound on central context, scope is bypassed.
- [ ] **Step 2:** Run, expect FAIL.
- [ ] **Step 3:** Implement `TenantManager` (singleton holding current Tenant, `set()`, `current()`, `hasTenant()`, `forgetTenant()`). Implement `BelongsToTenant` trait: `bootBelongsToTenant()` adds global scope filtering by `tenant_id` when a tenant is bound, and a `creating` hook auto-filling `tenant_id`. Register `TenancyServiceProvider` binding `TenantManager` as singleton.
- [ ] **Step 4:** Run, expect PASS.
- [ ] **Step 5:** Commit.

### Task 3: Add tenant_id to all tenant-owned tables

**Files:**
- Create: `database/migrations/2026_06_20_000002_add_tenant_id_to_tenant_tables.php`
- Modify models (add trait): `User.php`, `CustomerProfile.php`, `Package.php`, `PreAlert.php`, `PackageStatusHistory.php`, `ShippingRate.php`, `ContactMessage.php`, `WarehouseAddress.php`, `ActivityLog.php`, `AuthorisedPickupPerson.php`
- Test: `tests/Feature/Tenancy/CrossTenantIsolationTest.php`

- [ ] **Step 1: Write the failing test** — two tenants each with a customer + package; bound as tenant A, customer/package lists never include tenant B's rows; direct `find()` of tenant B's package id returns null under tenant A scope.
- [ ] **Step 2:** Run, expect FAIL.
- [ ] **Step 3:** Migration adds nullable-then-backfill `tenant_id` foreign key to each table with index. (Backfill: assign all existing rows to a seeded "Ship'd JM" tenant so production data survives.) Add `BelongsToTenant` to each model. Note: `User` needs care — platform owner has `tenant_id` null; tenant users have a tenant_id.
- [ ] **Step 4:** Run, expect PASS. Also re-run full suite; fix any existing tests that now need a tenant context.
- [ ] **Step 5:** Commit.

### Task 4: ResolveTenant middleware + central/tenant route split

**Files:**
- Create: `app/Http/Middleware/ResolveTenant.php`
- Create: `routes/tenant.php`, `routes/central.php`
- Modify: `bootstrap/app.php` (register middleware + route files), `routes/web.php` (split)
- Test: `tests/Feature/Tenancy/TenantResolutionTest.php`

- [ ] **Step 1: Write the failing test** — request to `acme.courieros.co` binds the Acme tenant; request to `courieros.co` (central) binds no tenant; unknown subdomain returns 404; suspended tenant returns a suspended page.
- [ ] **Step 2:** Run, expect FAIL.
- [ ] **Step 3:** Implement middleware: parse host, strip central domain suffix, look up tenant by subdomain (or custom_domain), call `TenantManager::set()`, share to Inertia. Central domain skips. Split routes: tenant portal/admin into `routes/tenant.php`, marketing/signup/platform-admin into `routes/central.php`. Register both in `bootstrap/app.php` with appropriate domain constraints.
- [ ] **Step 4:** Run, expect PASS.
- [ ] **Step 5:** Commit.

### Task 5: Per-tenant config resolution (replace global config)

**Files:**
- Modify: `config/shipdjm.php` → rename to `config/courieros.co`, keep as fallback defaults
- Create: `app/Support/Tenancy/TenantConfig.php` — resolves currency, prefixes, whatsapp from current tenant with config fallback
- Modify: `CustomerReferenceGenerator.php`, `PackageReferenceGenerator.php`, `HandleInertiaRequests.php` (brand block from tenant)
- Test: `tests/Feature/Tenancy/TenantConfigTest.php`

- [ ] **Step 1: Write the failing test** — reference generator uses the current tenant's prefix; Inertia `brand` shared props reflect the current tenant's currency/name/logo; falls back to config when no tenant.
- [ ] **Step 2:** Run, expect FAIL.
- [ ] **Step 3:** Implement `TenantConfig` accessor. Update generators and Inertia middleware to pull from it.
- [ ] **Step 4:** Run, expect PASS.
- [ ] **Step 5:** Commit.

---

## Phase 2: Platform Billing (Stripe / Cashier)

### Task 6: Install Cashier, tenant subscription scaffolding

**Files:**
- Modify: `composer.json` (add `laravel/cashier`)
- Create: migration for Cashier columns on `tenants` (Cashier billable = Tenant)
- Modify: `app/Models/Tenant.php` (add `Billable` trait)
- Create: `config/services.php` stripe block (keys via env)
- Test: `tests/Feature/Billing/TenantBillableTest.php`

- [ ] **Step 1: Write failing test** — Tenant is Billable, can store a stripe_id, has trial logic.
- [ ] **Step 2:** Run, expect FAIL.
- [ ] **Step 3:** `composer require laravel/cashier`. Publish + adjust migration to add billing columns to `tenants` (not users). Add `Billable` to Tenant. Add Stripe env keys (`STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `STRIPE_PRICE_MONTHLY`, `STRIPE_PRICE_SETUP`).
- [ ] **Step 4:** Run, expect PASS.
- [ ] **Step 5:** Commit.

> **HUMAN INPUT NEEDED HERE:** Stripe publishable key, secret key, webhook secret, monthly price ID ($79), setup fee price ID ($350). Products to create in Stripe: **"CourierOS Starter"** (recurring $79/mo) and **"CourierOS Setup Fee"** (one-time $350).

### Task 7: Tenant signup + Stripe Checkout flow

**Files:**
- Create: `app/Http/Controllers/Central/TenantSignupController.php`
- Create: `app/Http/Controllers/Central/BillingController.php`
- Create: `app/Actions/Tenancy/CreateTenant.php`
- Create: Vue `resources/js/pages/central/Signup.vue`, `central/BillingSuccess.vue`
- Test: `tests/Feature/Billing/TenantSignupTest.php`

- [ ] **Step 1: Write failing test** — signup creates a pending tenant + owner user, redirects to Stripe Checkout (setup fee + subscription); on successful checkout webhook the tenant becomes active.
- [ ] **Step 2:** Run, expect FAIL.
- [ ] **Step 3:** Implement signup form (business name, desired subdomain with availability check, owner name/email/password, currency, reference prefix). `CreateTenant` action provisions tenant (pending) + owner user + seed warehouse/rate placeholders. Checkout combines one-time setup price + recurring subscription. Build Vue pages.
- [ ] **Step 4:** Run, expect PASS.
- [ ] **Step 5:** Commit.

### Task 8: Stripe webhook → tenant lifecycle

**Files:**
- Create: `app/Http/Controllers/Webhooks/StripeWebhookController.php` (extend Cashier)
- Modify: `routes/central.php`
- Test: `tests/Feature/Billing/StripeWebhookTest.php`

- [ ] **Step 1: Write failing test** — `checkout.session.completed` activates tenant; `customer.subscription.deleted` suspends tenant; `invoice.payment_failed` flags past_due.
- [ ] **Step 2:** Run, expect FAIL.
- [ ] **Step 3:** Implement webhook handlers mapping Stripe events to tenant status. Verify signature with webhook secret.
- [ ] **Step 4:** Run, expect PASS.
- [ ] **Step 5:** Commit.

### Task 9: Subscription guard middleware

**Files:**
- Create: `app/Http/Middleware/EnsureTenantSubscribed.php`
- Modify: `routes/tenant.php`
- Test: `tests/Feature/Billing/SubscriptionGuardTest.php`

- [ ] **Step 1: Write failing test** — active subscription → access granted; suspended/cancelled → redirect to a billing-required page; trial still within window → granted.
- [ ] **Step 2:** Run, expect FAIL.
- [ ] **Step 3:** Implement guard, apply to tenant admin/portal route groups.
- [ ] **Step 4:** Run, expect PASS.
- [ ] **Step 5:** Commit.

---

## Phase 3: Platform Super-Admin (central domain)

### Task 10: Platform owner role + tenant management dashboard

**Files:**
- Create: `app/Http/Controllers/Central/PlatformAdmin/TenantController.php`
- Create: `app/Http/Controllers/Central/PlatformAdmin/DashboardController.php`
- Create: Vue `resources/js/pages/central/admin/Tenants.vue`, `central/admin/Dashboard.vue`
- Modify: `User.php` (add `ROLE_PLATFORM_OWNER` with null tenant_id)
- Test: `tests/Feature/Central/PlatformAdminTest.php`

- [ ] **Step 1: Write failing test** — platform owner sees all tenants, MRR, active/suspended counts; a tenant owner cannot access central admin.
- [ ] **Step 2:** Run, expect FAIL.
- [ ] **Step 3:** Implement platform dashboard (tenant list, status, MRR sum from subscriptions, signups over time), tenant detail (suspend/reactivate, impersonate optional later). Guard with platform-owner check (bypasses tenant scope).
- [ ] **Step 4:** Run, expect PASS.
- [ ] **Step 5:** Commit.

---

## Phase 4: WhatsApp Notifications

### Task 11: WhatsApp Cloud API client + channel

**Files:**
- Create: `app/Services/WhatsApp/WhatsAppClient.php`
- Create: `app/Notifications/Channels/WhatsAppChannel.php`
- Modify: existing notifications to add `whatsapp` to `via()` when tenant has a number
- Modify: `config/services.php` (whatsapp block)
- Test: `tests/Feature/Notifications/WhatsAppChannelTest.php`

- [ ] **Step 1: Write failing test** — when a tenant has WhatsApp configured, a package-status notification dispatches to the WhatsApp channel with the customer's number and rendered template; client is faked, asserts payload shape.
- [ ] **Step 2:** Run, expect FAIL.
- [ ] **Step 3:** Implement Meta Cloud API client (send template message), channel that reads recipient `whatsapp_number`, wire into notifications. Use per-tenant credentials.
- [ ] **Step 4:** Run, expect PASS.
- [ ] **Step 5:** Commit.

> **HUMAN INPUT NEEDED:** Meta WhatsApp Cloud API token, phone number ID, and approved message template names. (Can be deferred — feature ships behind tenant config; tenants without creds keep the existing manual WhatsApp link.)

### Task 12: Customer status-change notifications end to end

**Files:**
- Modify: `PackageStatusRecorder.php` / status update controllers to fire notifications
- Test: `tests/Feature/Notifications/PackageStatusNotificationTest.php`

- [ ] **Step 1: Write failing test** — moving a package to Ready for Pickup sends email + (if configured) WhatsApp; respects tenant.
- [ ] **Step 2:** Run, expect FAIL.
- [ ] **Step 3:** Wire notifications into status transitions; ensure queued.
- [ ] **Step 4:** Run, expect PASS.
- [ ] **Step 5:** Commit.

---

## Phase 5: Reporting & Analytics (tenant admin)

### Task 13: Tenant reports dashboard

**Files:**
- Create: `app/Http/Controllers/Admin/ReportsController.php`
- Create: `app/Services/Reporting/TenantReportService.php`
- Create: Vue `resources/js/pages/admin/reports/Index.vue`
- Modify: tenant admin nav, `routes/tenant.php`
- Test: `tests/Feature/Admin/ReportsTest.php`

- [ ] **Step 1: Write failing test** — report returns revenue by month, package volume, unpaid total, top customers — all scoped to current tenant only.
- [ ] **Step 2:** Run, expect FAIL.
- [ ] **Step 3:** Implement aggregation service + controller + chart UI (reuse existing chart.js/vue-chartjs already in deps). CSV export endpoint.
- [ ] **Step 4:** Run, expect PASS.
- [ ] **Step 5:** Commit.

---

## Phase 6: Branding & Multi-Currency

### Task 14: Per-tenant branding (name, logo, colors)

**Files:**
- Create: `app/Http/Controllers/Admin/BrandingController.php`
- Create: Vue `resources/js/pages/admin/settings/Branding.vue`
- Modify: `PublicLayout.vue`, `AppLayout.vue`, all `public/*.vue` pages (replace hardcoded "Ship'd JM")
- Test: `tests/Feature/Admin/BrandingTest.php`

- [ ] **Step 1: Write failing test** — tenant can set name/logo/primary color; public + portal pages render tenant brand; defaults applied when unset.
- [ ] **Step 2:** Run, expect FAIL.
- [ ] **Step 3:** Implement branding settings (logo upload to tenant-scoped storage path), inject brand into Inertia, replace hardcoded strings with brand props across Vue pages.
- [ ] **Step 4:** Run, expect PASS.
- [ ] **Step 5:** Commit.

### Task 15: Multi-currency (USD + JMD)

**Files:**
- Modify: `PackageChargeCalculator.php`, `ShippingRatePresenter`, money formatting helpers
- Create: `app/Support/Money.php` (format per tenant currency)
- Test: `tests/Feature/Tenancy/MultiCurrencyTest.php`

- [ ] **Step 1: Write failing test** — a USD tenant displays `US$`, a JMD tenant displays `JMD $`; charge calc respects tenant currency; rates store currency.
- [ ] **Step 2:** Run, expect FAIL.
- [ ] **Step 3:** Centralize money formatting through `Money` helper driven by tenant currency; update calculators and presenters; surface to frontend.
- [ ] **Step 4:** Run, expect PASS.
- [ ] **Step 5:** Commit.

---

## Phase 7: Central Marketing Site, SEO & Ads Readiness

### Task 16: CourierOS marketing site

**Files:**
- Create: Vue `resources/js/pages/central/marketing/Home.vue`, `Pricing.vue`, `Features.vue`, `Contact.vue`
- Create: `app/Http/Controllers/Central/MarketingController.php`
- Modify: `routes/central.php`
- Test: `tests/Feature/Central/MarketingPagesTest.php`

- [ ] **Step 1: Write failing test** — central marketing routes return 200 with correct Inertia components; pricing page shows $79/mo + $350 setup; CTA links to signup.
- [ ] **Step 2:** Run, expect FAIL.
- [ ] **Step 3:** Build marketing pages targeting the buyer (Jamaican courier business owner). Hero, how it works, pricing, features, FAQ, signup CTA. Apply `frontend-design` skill for distinctive visual design.
- [ ] **Step 4:** Run, expect PASS.
- [ ] **Step 5:** Commit.

### Task 17: SEO foundation (meta, schema, sitemap, robots, llms.txt)

**Files:**
- Create: `app/Http/Controllers/Central/SitemapController.php`
- Create: `app/Support/Seo/SeoMeta.php` (per-page title/description/OG/canonical)
- Modify: `resources/views/app.blade.php` (meta block, JSON-LD), `public/robots.txt`
- Create: `public/llms.txt`
- Test: `tests/Feature/Central/SeoTest.php`

- [ ] **Step 1: Write failing test** — marketing pages emit unique titles/descriptions, canonical URLs, OG tags, and `SoftwareApplication`/`Organization` JSON-LD; `/sitemap.xml` lists marketing pages; robots allows crawl.
- [ ] **Step 2:** Run, expect FAIL.
- [ ] **Step 3:** Implement SEO meta helper, inject per-page, add JSON-LD, dynamic sitemap, robots, llms.txt (mirrors the InvoiceFeed approach you've used before).
- [ ] **Step 4:** Run, expect PASS.
- [ ] **Step 5:** Commit.

### Task 18: Ads readiness (analytics + conversion tracking hooks)

**Files:**
- Modify: `resources/views/app.blade.php` (GA4 + Google Ads / Meta Pixel slots, env-gated)
- Create: `app/Support/Analytics/ConversionEvents.php` (signup, checkout-complete server events)
- Modify: `config/services.php` (analytics IDs via env)
- Test: `tests/Feature/Central/AnalyticsTest.php`

- [ ] **Step 1: Write failing test** — when analytics env IDs are set, marketing pages include the tags; signup + paid conversion fire a tracked event; absent IDs render nothing.
- [ ] **Step 2:** Run, expect FAIL.
- [ ] **Step 3:** Add env-gated GA4 + Google Ads conversion + Meta Pixel slots; server-side conversion event on successful Stripe checkout (for accurate ad attribution); document required env keys.
- [ ] **Step 4:** Run, expect PASS.
- [ ] **Step 5:** Commit.

> **HUMAN INPUT (deferred, ads phase):** GA4 measurement ID, Google Ads conversion ID/label, Meta Pixel ID.

---

## Phase 8: Data Migration, Seeding & Deploy Prep

### Task 19: Ship'd JM seed tenant + demo seeder rework

**Files:**
- Modify: `database/seeders/*` — wrap demo data in a tenant context
- Create: `database/seeders/ShipdjmTenantSeeder.php`
- Test: `tests/Feature/Tenancy/SeederTest.php`

- [ ] **Step 1: Write failing test** — seeding creates at least one tenant with its full demo dataset, all rows carry the tenant_id.
- [ ] **Step 2:** Run, expect FAIL.
- [ ] **Step 3:** Rework seeders to be tenant-aware; create the canonical Ship'd JM tenant for the existing client.
- [ ] **Step 4:** Run, expect PASS.
- [ ] **Step 5:** Commit.

### Task 20: Browser/QA pass (Playwright) + deploy checklist

**Files:**
- Create: `docs/courieros/qa-checklist.md`
- Create: `docs/courieros/deploy.md` (Forge + Cloudflare wildcard subdomain `*.courieros.co`, DNS, SSL, queue worker, scheduler, Stripe webhook URL, Resend domain)

- [ ] **Step 1:** Run full automated suite green. `php artisan test`
- [ ] **Step 2:** Browser-test core journeys via Playwright on the local build: tenant signup → checkout (Stripe test mode) → tenant subdomain login → create customer → pre-alert → package → status change → notification → invoice. Plus cross-tenant isolation spot check.
- [ ] **Step 3:** Write QA checklist + deploy doc (wildcard DNS, Forge site config, env keys, webhook endpoints).
- [ ] **Step 4:** Commit.

---

## Final Review

- [ ] Dispatch final code-reviewer subagent over the entire implementation.
- [ ] Use superpowers:finishing-a-development-branch to open the PR against the new CourierOS repo.

---

## Human Input Summary (collected as we go, none block early phases)

| Needed at | What |
|---|---|
| Task 6–8 | Stripe keys (publishable, secret, webhook secret) + price IDs for "CourierOS Starter" $79/mo and "CourierOS Setup Fee" $350 |
| Task 11 | Meta WhatsApp Cloud API token, phone number ID, template names (deferrable) |
| Task 1–5 | Resend API key + verified sending domain for courieros.co |
| Task 18 | GA4 ID, Google Ads conversion ID/label, Meta Pixel ID (deferrable) |
| Task 20 | New CourierOS GitHub repo URL; confirm Forge access for wildcard subdomain deploy |

Phases 1, 3, 5, 6, 7 (most of the build) need **no** external keys and can proceed immediately.
