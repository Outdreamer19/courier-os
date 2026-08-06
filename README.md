# CourierOS

Multi-tenant SaaS for Caribbean courier and freight-forwarding businesses.
Each tenant gets their own branded site where their customers register, get a
Miami warehouse address, pre-alert shipments, track packages, and pay invoices
— while the courier runs intake, status updates, billing and reporting from an
admin portal.

One Laravel application serves three surfaces:

| Surface | Host | What it is |
| --- | --- | --- |
| **Platform** | `courieros.co` | CourierOS marketing site, tenant signup, Stripe checkout, and the owner console at `/platform` |
| **Tenant public site** | `{subdomain}.courieros.co` | The courier's own marketing site, rates and contact form |
| **Tenant portal** | `{subdomain}.courieros.co/admin` and `/portal` | Courier admin operations, and their customers' portal |

Built on Laravel 12 + Vue 3 + Inertia (Fortify, Cashier, Wayfinder,
Tailwind v4, reka-ui, TypeScript).

---

## How tenancy works

Everything hangs off the request host.

1. `ResolveTenant` (prepended to the `web` group) reads `$request->getHost()`,
   strips `COURIEROS_CENTRAL_DOMAIN`, and looks the remaining label up against
   `tenants.subdomain`. `tenants.custom_domain` is checked first, so a courier
   can bring their own domain without a code change.
2. The apex and any reserved subdomain (`www`, `app`, `admin`, `billing`, …
   see `config/courieros.php`) bind no tenant and run in **central** context.
3. A resolved tenant is bound into the `TenantManager` singleton. The
   `BelongsToTenant` trait adds a global scope filtering every tenant-owned
   model by `tenant_id`, and auto-fills `tenant_id` on create.
4. With no tenant bound the scope is a **no-op** — which is what lets platform
   code and CLI commands see across every tenant.

Guards layered on top:

| Middleware | Alias | Enforces |
| --- | --- | --- |
| `EnsureUserBelongsToTenant` | `tenant.member` | A user may only act inside their own tenant. Platform owners are barred from tenant subdomains entirely. |
| `EnsureTenantSubscribed` | `tenant.subscribed` | The tenant has an active subscription, an in-window trial, or active status. |
| `EnsureUserRole` | `role:owner,admin,staff` | Coarse role gate. |
| `EnsureAdminPermission` | `admin.permission:manage_billing` | Fine-grained admin capability. |
| `EnsurePlatformOwner` | `platform` | CourierOS owner, in central context only. |
| `EnsureUserIsActive` | `active` (also global) | Suspended users are signed out on every request. |

`SESSION_DOMAIN` must stay `null`. A null value scopes the session cookie to
the exact host, which is what keeps each tenant's session isolated. Setting it
to `.courieros.co` would share one session across every tenant on the
platform.

---

## Roles

| Role | Scope | Can |
| --- | --- | --- |
| `platform_owner` | Central, no tenant | Platform console: revenue, tenant health, suspend/reactivate tenants |
| `owner` | One tenant | Everything in their tenant, including admin user management |
| `admin` | One tenant | Operations and billing |
| `staff` | One tenant | Day-to-day packages and pre-alerts |
| `customer` | One tenant | Their own pre-alerts, packages and invoices |

---

## Local setup

Requirements: PHP 8.3+, Composer 2, Node 22+, MySQL 8. Herd ships all of it.

```bash
cd ~/Herd/courieros

composer install
npm install
cp .env.example .env            # only if .env does not already exist
php artisan key:generate

mysql -uroot -e "CREATE DATABASE IF NOT EXISTS courieros CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

php artisan migrate --seed
npm run build                   # or `npm run dev` for HMR
```

Herd resolves `*.test`, including wildcards, so `courieros.test` and
`today.courieros.test` both work with no extra DNS config. Set
`COURIEROS_CENTRAL_DOMAIN=courieros.test` in `.env` to match.

### Seeding

```bash
php artisan db:seed --class=CourierOsSeeder            # platform owner + 3 tenants
php artisan db:seed --class=TodayShippingDataSeeder    # + realistic Today Shipping data
```

`TodayShippingDataSeeder` calls `CourierOsSeeder` first, so it is the only one
you need for a full local environment. It is idempotent.

### Seeded accounts

All use the password `password`.

| Host | Email | Role |
| --- | --- | --- |
| `courieros.test` | `platform@courieros.co` | Platform owner → `/platform` |
| `today.courieros.test` | `admin@todayshippingja.com` | Owner (the pilot client's real address) |
| `today.courieros.test` | `owner@today.test` | Owner |
| `today.courieros.test` | `admin@today.test` | Admin |
| `today.courieros.test` | `staff@today.test` | Staff |
| `today.courieros.test` | `customer@today.test` | Customer |
| `shipd.courieros.test` | `owner@shipd.test` / `customer@shipd.test` | Owner / customer |
| `island.courieros.test` | `owner@island.test` / `customer@island.test` | Owner / customer |

---

## Useful commands

```bash
php artisan test                # 214 feature + unit tests (sqlite in-memory)
./vendor/bin/pint               # PHP formatting
npm run lint                    # ESLint
npm run format                  # Prettier
npm run types:check             # vue-tsc

# Provision a staff/owner account inside a tenant, ready to sign in.
# Prints a generated password once; marks the address verified.
php artisan tenant:user today admin@todayshippingja.com --role=owner

# Wipe a tenant's operational data but keep its config, rates and staff.
php artisan tenant:reset today --force

# Reconcile payment status with InvoiceFeed (also scheduled hourly).
php artisan shipdjm:sync-invoicefeed-payments
```

---

## Project layout

```
app/
├── Http/Middleware/ResolveTenant.php        # host → tenant binding
├── Http/Controllers/Central/                # platform: signup, billing, console
├── Http/Controllers/Admin/                  # tenant admin operations
├── Http/Controllers/Customer/               # tenant customer portal
├── Models/Concerns/BelongsToTenant.php      # the global scope
├── Support/Tenancy/{TenantManager,TenantConfig}.php
├── Services/Platform/PlatformStatsService.php
└── Console/Commands/{ProvisionTenantUser,ResetTenantData,SyncInvoiceFeedPayments}.php

resources/js/
├── layouts/{AppLayout,PublicLayout,AuthLayout,PlatformLayout}.vue
├── components/marketing/                    # CourierOS landing page
├── pages/central/                           # platform (no layout — self-contained)
├── pages/public/                            # tenant marketing site
├── pages/admin/                             # tenant admin
└── pages/customer/                          # tenant customer portal

resources/views/
├── tenants/today-shipping.blade.php         # bespoke tenant landing page
└── errors/                                  # branded, dependency-free error pages

routes/
├── web.php                                  # tenant public + portal + admin
├── central.php                              # platform-only routes
└── console.php                              # scheduled tasks
```

Tenants with a bespoke marketing page are mapped in
`PublicPageController::CUSTOM_HOME_VIEWS`; everyone else gets the shared
`public/Home` Inertia page driven by their own rates and warehouse.

---

## Deployment

- Production env template: [`.env.production.example`](.env.production.example)
- Client UAT runbook: [`docs/deployment/today-shipping-uat.md`](docs/deployment/today-shipping-uat.md)
- **Launch readiness audit: [`docs/launch-readiness.md`](docs/launch-readiness.md)** — read this before going live

Tenant subdomains require a wildcard DNS record (`A * → server IP`), a
`*.courieros.co` alias on the Forge site, and a wildcard TLS certificate
issued via a DNS-01 challenge. Turn on the Forge scheduler so the hourly
payment reconciliation runs.
