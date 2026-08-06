# CourierOS — launch readiness audit

**Date:** 5 August 2026
**Branch reviewed:** `feat/courieros-marketing-landing`
**Scope:** multi-tenancy, auth, billing, platform console, tenant portal,
marketing site, configuration, deployment.

This is a snapshot of what is genuinely ready, what is fixed in this pass, and
what still stands between you and paying customers. Items are ordered by what
would hurt most if you launched without them.

---

## Verdict in one paragraph

The product itself is in good shape. Tenancy isolation is real and tested,
the tenant admin and customer portals are complete and branded, and the pilot
tenant works end to end on a subdomain. What is not ready is the **commercial
layer**: nothing actually enforces payment, Stripe is unconfigured, mail is
untested in production, and there is no wildcard DNS or TLS for tenant
subdomains. You can run a pilot today. You cannot take money today.

---

## P0 — blocks launch

### 1. Nothing enforces payment

`EnsureTenantSubscribed` treats *any* tenant with `status = active` as
entitled:

```php
$entitled = $tenant->subscribed('default')
    || $tenant->onGenericTrial()
    || $tenant->isActive();   // ← this makes the other two irrelevant
```

Every tenant you activate manually — including every pilot — has full access
forever, with no subscription. That fallback was the right call while billing
was being built; it is a revenue hole the day you charge anyone.

**Fix when you flip billing on:** drop the `isActive()` term and give pilots an
explicit `trial_ends_at` instead. Deliberately *not* changed here, because
doing so today would lock Today Shipping out mid-pilot.

### 2. Stripe is not configured

`STRIPE_PRICE_MONTHLY` and `STRIPE_PRICE_SETUP` are empty. `/signup` collects
a business, creates a pending tenant, then redirects to
`central.billing.checkout` — which cannot build a Checkout session without
price IDs. **Public signup is currently a dead end.** Create both prices in
Stripe, set the env vars, and walk one signup through end to end in test mode.

Also register the webhook endpoint (`/stripe/webhook`) and set
`STRIPE_WEBHOOK_SECRET` — without it, `checkout.session.completed` never
fires, so paid tenants stay `pending` and never gain access.

### 3. Wildcard DNS and TLS for tenant subdomains

Per `docs/deployment/today-shipping-uat.md`, `today.courieros.co` has no DNS
record at all. Tenant subdomains are the entire distribution model, so this
blocks every tenant, not just the pilot.

- `A * → <server IP>` on `courieros.co`
- Add the `*.courieros.co` alias to the Forge site
- Issue a wildcard cert via DNS-01 (Forge + Cloudflare API token)

### 4. Mail is unproven in production

`/dashboard` sits behind Laravel's `verified` middleware. If Resend is not
verified for the sending domain, a new customer registers, never receives the
link, and is permanently stuck — which reads to the client as "the product is
broken". Publish SPF and DKIM, then send a real test message before handing
over any environment.

---

## P1 — fix in the first week

### 5. Notifications send synchronously

No notification in `app/Notifications` implements `ShouldQueue`. Every package
status change blocks the admin's HTTP request on a Resend API call (and a
WhatsApp call, where configured). At volume the admin UI will feel sluggish,
and a mail provider outage surfaces as a 500 on a normal admin action.

**Fix:** add `implements ShouldQueue` to the three notification classes —
*and only then* start a queue worker on the server. Queuing without a running
worker is worse than not queuing: notifications would silently never send.
Left unchanged here for exactly that reason; it needs the Forge worker in the
same change.

### 6. No error monitoring

`withExceptions()` in `bootstrap/app.php` is empty and there is no Sentry,
Bugsnag or Flare integration. In production with `APP_DEBUG=false`, a customer
hitting a 500 produces a log line on a server nobody is watching. Add
something before real users arrive.

### 7. No backups verified

Nothing in the repo documents database backups. Tenant data is customers'
shipment history — losing it ends the business relationship. Turn on Forge's
database backups to S3 and restore one to a scratch database to prove it
works.

### 8. Rate limiting is thin

Login (5/min), contact (10/min), signup (10/min) and the rate calculator
(60/min) are limited. Nothing limits authenticated admin endpoints or the
InvoiceFeed webhook. Lower priority since both webhooks verify signatures, but
worth a global throttle.

---

## P2 — should do, not blocking

| Item | Why it matters |
| --- | --- |
| `today-shipping-site/` (14 MB) still in the repo root | Superseded static prototype; the live page is `resources/views/tenants/today-shipping.blade.php`. Dead weight in every clone and deploy. |
| Tenant custom domains are a tinker command | `ResolveTenant` supports `custom_domain` already, but setting it means a manual DB write. Worth a field on the platform Tenants page before the second tenant asks. |
| MRR is derived from a config constant | `PlatformStatsService` multiplies active subscriptions by `courieros.pricing.monthly`. Correct while there is one plan; it will quietly lie the moment you add a second tier or discount anyone. |
| No `Model::shouldBeStrict()` | Lazy-loading and missing-attribute bugs ship silently instead of failing loudly in development. |
| `config/shipdjm.php` still referenced | Ten files still read `config('shipdjm.*')` for reference prefixes and invoice settings. Harmless, but it is the old single-tenant product's name leaking through a multi-tenant codebase. |

---

## Fixed in this pass

| Area | What was wrong | What changed |
| --- | --- | --- |
| **Platform owner login** | `platform@courieros.co` landed on the *customer* dashboard — warehouse address, pre-alerts, "amount due" — because `isPlatformOwner()` is not in `adminRoles()`, so the redirect never fired. | `DashboardController` now redirects platform owners to `/platform`. |
| **Platform console layout** | `central/admin/*` pages fell through to the default Inertia layout and rendered inside the tenant customer sidebar ("My shipping address" next to platform MRR). | `app.ts` now returns `null` for all `central/` pages; the console brings its own `PlatformLayout`. |
| **Platform console content** | Six numbers and a bar chart. | Rebuilt: MRR/ARR/ARPA, subscription states, tenant pipeline, platform-wide throughput, a per-tenant health board, and an "needs your attention" list (stalled onboarding, past-due billing, trials ending, churn risk). |
| **White-label leak (titles)** | Every tenant page title read `… - CourierOS`, because the title used the build-time `VITE_APP_NAME`. Their customers saw your product's name. | Title now reads the per-request `brand.name` shared by `HandleInertiaRequests`. |
| **White-label leak (signup)** | `routes/central.php` is registered on the shared `web` group, so `/signup` and `/billing/*` answered on **every tenant subdomain**. Today Shipping's own customers could land on `todayshippingja.com/signup` and be pitched — CourierOS branding, $79/mo pricing and all — on starting a competing courier platform. | New `EnsureCentralContext` middleware (`central` alias) 404s those routes when a tenant is bound. The Stripe webhook is deliberately left outside it. |
| **White-label leak (errors)** | Error pages showed "CourierOS" on tenant domains. | They now resolve the tenant's own name via `TenantConfig`. |
| **Marketing hero** | Flat cream background and an empty "Screenshot coming soon" placeholder. | Layered gradient mesh, drifting colour fields, masked dot grid, animated freight routes, and a rendered product mock of both the admin dashboard and customer portal. All motion respects `prefers-reduced-motion`. |
| **Hero first paint** | The hero used `fade-in-section`, so the largest element on the page stayed at opacity 0 until the JS bundle booted and the IntersectionObserver fired. | Replaced with pure-CSS `hero-rise`, which runs on first paint. |
| **Error pages** | None existed. A suspended tenant's customers got a bare Symfony "403 Forbidden". | Branded 403/404/419/429/500/503, dependency-free so they still render when the app itself is broken. The 403 surfaces the specific middleware message. |
| **Proxy trust** | No trusted proxies configured. Behind Forge's nginx (and Cloudflare) Laravel sees plain HTTP, breaking secure cookies and generating `http://` URLs on an HTTPS site. | `trustProxies(at: '*')` in `bootstrap/app.php`. |
| **Payment reconciliation** | `shipdjm:sync-invoicefeed-payments` existed but was never scheduled, so a dropped webhook meant a paid package showed "unpaid" forever. | Scheduled hourly in `routes/console.php`. Requires the Forge scheduler to be on. |
| **Reserved subdomains** | Six names reserved. A tenant could have claimed `billing`, `support`, `docs`, `status`. | Expanded to cover infrastructure, platform surfaces, content and environment names. Reclaiming one after a tenant takes it means migrating their URL. |
| **Pilot owner account** | The runbook told you to create it with a hand-written `tinker --execute` one-liner — easy to get wrong (forget the tenant scope and you edit another tenant's user; forget `email_verified_at` and they are stuck on the verification screen). | New `php artisan tenant:user` command, plus the pilot account is now seeded. |
| **Local `.env`** | `APP_NAME="Ship'd JM"` leaked into the browser tab on `courieros.co`; `MAIL_FROM_ADDRESS` pointed at `shipdjm.com`. | Both corrected. **The production server still needs the same edit.** |
| **Config** | Platform pricing was a hardcoded `private const MONTHLY_PRICE = 79` inside the stats service. | Moved to `config/courieros.php` + `COURIEROS_PRICE_*` env vars, documented in `.env.production.example`. |

---

## What is genuinely solid

Worth saying plainly, because it is the majority of the system:

- **Tenant isolation.** `BelongsToTenant` global scope plus `ResolveTenant`,
  `EnsureUserBelongsToTenant` and host-scoped session cookies
  (`SESSION_DOMAIN=null`). Covered by `CrossTenantIsolationTest` and
  `TenantScopingTest`.
- **Authorisation.** Four roles, granular admin permissions via
  `admin.permission:*`, suspended users bounced on every request.
- **Webhooks.** Both Stripe and InvoiceFeed verify signatures before doing any
  work and are correctly CSRF-exempt.
- **Secrets.** `.env` is untracked, WhatsApp tokens are encrypted at rest,
  password rules tighten automatically in production (12 chars, mixed case,
  symbols, breach-checked).
- **Test coverage.** 214 feature and unit tests, with CI running lint and
  tests on push.
- **The tenant product.** Pre-alerts, package intake, status timelines,
  invoicing, reports, activity log, branding, admin user management — all
  present and working against seeded data.

---

## Suggested order of work

1. Wildcard DNS + TLS (unblocks every tenant)
2. Verify Resend sending domain, send a real test
3. Fix production `.env` (`APP_NAME`, `MAIL_FROM_ADDRESS`, `COURIEROS_PRICE_*`)
4. Configure Stripe prices + webhook, walk one signup through in test mode
5. Turn on the Forge scheduler
6. Add error monitoring and verify a database backup restore
7. Queue the notifications *and* start a worker, in the same change
8. Remove the `isActive()` fallback in `EnsureTenantSubscribed` and put pilots
   on an explicit trial window
