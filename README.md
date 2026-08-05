# SHIP DJM

Shipping & freight forwarding portal for Jamaica-bound packages. Customers ship to a Florida warehouse, pre-alert their shipments, and receive consolidated freight on the island. Phase 1 ships the foundation: public marketing site, authentication, role-based access, and dashboard shells.

Built on the Laravel 12 + Vue 3 + Inertia.js starter kit (Fortify, Wayfinder, Tailwind v4, reka-ui, TypeScript).

---

## Phase 1 scope (delivered)

- **Public site:** Home, About, Rates (with calculator), Contact (functional form), Terms, Privacy, Shipping Policy, Refund Policy, Restricted Items.
- **Brand:** black / gold / green / cream tokens applied across CSS, layouts, auth pages, and dashboards.
- **Auth:** Fortify-driven register / login / forgot password / reset / verify, all reskinned.
- **Users:** `role` (admin | customer) and `status` (active | suspended) columns. Suspended users are signed out and bounced on every protected request.
- **Customer references:** `DJM-000001` style identifier created in a locked transaction whenever a user registers.
- **Warehouse + rates:** Editable warehouse address and JMD 500/lb default shipping rate, seeded and shared to every Inertia page.
- **Dashboards:**
  - Customer dashboard with reference, copy-ready Florida warehouse address, and empty-state cards for packages, pre-alerts, and amount due.
  - Admin dashboard with customer counts, placeholder package/pre-alert metrics, and recent contact messages.
- **Contact inbox:** Public form persists to `contact_messages` and surfaces on the admin dashboard.
- **Automated coverage:** 59 PHPUnit feature tests (Pint + ESLint clean).

Future phases (pre-alerts, package intake, invoicing, notifications, etc.) are intentionally out of scope here.

---

## Requirements

- PHP 8.3+ (Herd ships this)
- Composer 2.x
- Node 22+ and npm 10+
- MySQL 8 (Herd's bundled MySQL works out of the box)

---

## Local setup

```bash
cd ~/Herd/shipdjm

composer install
npm install

cp .env.example .env   # only if .env does not already exist
php artisan key:generate

# Create the MySQL schema (Herd MySQL listens on 127.0.0.1:3306 with the default `root` user, no password)
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS shipdjm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

php artisan migrate --seed
npm run build           # generates the Vite manifest used by Inertia
```

Serve the app through Herd (`https://shipdjm.test`) or run `php artisan serve` + `npm run dev` for a hot-reloading workflow.

### Seeded credentials

| Role      | Email                    | Password   | Reference   |
| --------- | ------------------------ | ---------- | ----------- |
| Admin     | `shane1obdurate@gmail.com` | `password` | n/a         |
| Customer  | `customer@shipdjm.test`  | `password` | `DJM-000001` |

The seeder also provisions the placeholder Florida warehouse and a single active JMD 500/lb shipping rate.

---

## Useful commands

```bash
# Run the test suite (PHPUnit feature + unit)
php artisan test

# PHP code style
./vendor/bin/pint           # apply fixes
./vendor/bin/pint --test    # dry-run

# Frontend lint + format
npm run lint                # apply fixes
npm run format              # Prettier
npm run format:check        # Prettier dry-run
npx eslint . --max-warnings=0   # strict CI-style check

# Build assets
npm run dev                 # HMR dev server
npm run build               # production build (required before php artisan test if you cleared public/build)
```

---

## Manual smoke-test checklist

Run after `php artisan migrate:fresh --seed` and `npm run build`.

### Central marketing site (courieros.co, logged out)
- [ ] `/` on the central domain (not a tenant subdomain) renders the CourierOS marketing page — sticky nav, hero, pricing ($79/mo + $349 setup), FAQ accordion, footer.
- [ ] `/` on a tenant subdomain still renders that tenant's own public home page (regression check).
- [ ] `/signup` renders full-bleed with no dashboard sidebar around it.

### Public site (logged out)
- [ ] `/` renders hero, three-step "how it works", rate preview, FAQ, and final CTA.
- [ ] `/about` renders mission, expectations, and values copy.
- [ ] `/rates` calculator returns `weight × 500 JMD` for several weights.
- [ ] `/contact` submission shows a success toast and stores a row in `contact_messages`.
- [ ] `/terms`, `/privacy`, `/shipping-policy`, `/refund-policy`, `/restricted-items` all load with the legal placeholder notice.
- [ ] Header CTAs link to `register` and `login`; footer legal links resolve.

### Authentication
- [ ] Register a brand-new account → redirects to customer dashboard with a fresh `DJM-XXXXXX` reference.
- [ ] Login as `customer@shipdjm.test` → lands on `/dashboard` with `DJM-000001`.
- [ ] Logout returns to `/`.
- [ ] Forgot password / reset password / verify email flows render with brand styling.

### Customer dashboard
- [ ] Shows customer name, `DJM-000001`, and welcome copy.
- [ ] Florida warehouse address card displays the seeded address; "copy" buttons place the value on the clipboard with a toast.
- [ ] Empty-state cards for packages, pre-alerts, and amount due are visible.

### Admin dashboard
- [ ] Login as `shane1obdurate@gmail.com` → `/dashboard` redirects to `/admin/dashboard`.
- [ ] Stat cards show "Customers" count (≥1) and placeholder pre-alert/package counts.
- [ ] Recent contact messages panel lists submissions made above.
- [ ] Sidebar shows the admin navigation set.

### Authorization
- [ ] As a customer, GET `/admin/dashboard` returns `403`.
- [ ] As a guest, GET `/dashboard` redirects to `/login`.
- [ ] Set a user's `status` to `suspended` (`php artisan tinker` → `User::find(2)->update(['status' => 'suspended'])`) and try to load `/dashboard`. The user is logged out and redirected to `/login` with the "Your account has been suspended" message.

### Shared data
- [ ] Hitting `/` populates the rate preview from the active `shipping_rates` row and the warehouse from the active `warehouse_addresses` row (change either in the DB and the public site reflects it after a refresh).

---

## Project layout (highlights)

```
app/
├── Actions/Fortify/CreateNewUser.php        # creates customer profile + reference on register
├── Http/Controllers/
│   ├── PublicPageController.php             # public marketing pages
│   ├── ContactController.php                # public contact form
│   ├── DashboardController.php              # role-aware authenticated landing
│   └── Admin/DashboardController.php
├── Http/Middleware/
│   ├── EnsureUserRole.php                   # alias `role:admin|customer`
│   ├── EnsureUserIsActive.php               # alias `active`, also global
│   └── HandleInertiaRequests.php            # shares brand / warehouse / flash
├── Models/
│   ├── User.php (role/status helpers)
│   ├── CustomerProfile.php
│   ├── WarehouseAddress.php
│   ├── ShippingRate.php
│   └── ContactMessage.php
└── Services/CustomerReferenceGenerator.php  # DJM-000001 generator

config/shipdjm.php                            # brand, currency, reference padding, invoice upload config

database/
├── migrations/2026_05_26_100*                # phase 1 schema
└── seeders/{WarehouseAddressSeeder, ShippingRateSeeder, UsersSeeder}.php

resources/
├── css/app.css                               # brand tokens
└── js/
    ├── layouts/PublicLayout.vue              # public marketing chrome
    ├── pages/public/                         # Home, About, Rates, Contact, legal/*
    ├── pages/Dashboard.vue                   # customer dashboard
    ├── pages/admin/Dashboard.vue             # admin dashboard
    ├── components/AppLogo.vue                # SHIP DJM wordmark
    └── components/CopyButton.vue             # reusable clipboard control

routes/web.php                                # public + auth + admin groups
tests/Feature/                                # PublicPagesTest, ContactFormTest, RoleAccessTest, CustomerReferenceGeneratorTest, InertiaSharedDataTest, ...
```

---

## Configuration knobs

`.env` (all optional, sensible defaults in `config/shipdjm.php`):

```dotenv
APP_NAME="SHIP DJM"
SHIPDJM_CURRENCY=JMD
SHIPDJM_DEFAULT_RATE_PER_LB=500
SHIPDJM_CUSTOMER_REFERENCE_PREFIX=DJM
SHIPDJM_CUSTOMER_REFERENCE_PADDING=6
```

The shipping rate displayed in marketing pages comes from the active row in `shipping_rates`. Editing the seeded row (or adding new tiers via tinker) changes the public preview and the calculator immediately.

The Florida warehouse address shown on the customer dashboard and the homepage comes from the active row in `warehouse_addresses`. To update it before the admin warehouse CRUD ships in a later phase, edit the seeded row via tinker or a one-off migration.

---

## Known follow-ups (next phases)

These are intentionally **not** implemented yet:

- Customer profile editor (Jamaica address, phone, parish).
- Pre-alerts (create / edit / cancel + tracking link).
- Package intake (admin) and customer package timeline.
- Invoicing (PDF + JMD totals + payment status).
- Notifications (email + WhatsApp link-outs).
- Admin CRUD for warehouses, rates, customers, and contact messages.
- Storage of invoice uploads on the configured disk (`config/shipdjm.php → invoice_uploads`).

Phase 1 lays the routing, authorization, shared data, and visual foundations so each of those slots into the existing layouts without rework.
