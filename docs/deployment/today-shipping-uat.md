# Today Shipping — client UAT setup runbook

Goal: Today Shipping's team can reach their own instance from their own
location, over the public internet, and exercise it exactly as they would in
production.

Target URL for now: **https://today.courieros.co**
Later: their own domain (see [Custom domain cutover](#7-custom-domain-cutover)).

---

## Current state (verified 2026-08-05)

| Check | Result |
| --- | --- |
| `https://courieros.co` | ✅ Serving the app (SSL now installed) |
| `https://today.courieros.co` | ❌ Chrome error page |
| `http://today.courieros.co` (plain HTTP) | ❌ Chrome error page |

Plain HTTP failing as well as HTTPS means this is **not** an SSL problem —
`today.courieros.co` has no DNS record at all. Step 1 is the blocker.

Deployed branch is `main`, which already contains `ResolveTenant`, the
`today` tenant seeder, and `resources/views/tenants/today-shipping.blade.php`.
No application changes are required to make the subdomain resolve.

---

## 1. DNS — wildcard record

At your DNS host for `courieros.co`, add a wildcard record pointing at the
same server as the apex:

```
Type   Name   Value                TTL
A      *      <your server IPv4>   300
```

Use the identical IP as the existing `@` / `courieros.co` record. A single
wildcard covers `today`, plus every future tenant, with no DNS change per
client.

If your DNS is on Cloudflare, set the wildcard record's proxy status to match
the apex record. If the apex is proxied (orange cloud), proxy the wildcard too
— otherwise the two will disagree on TLS termination.

Verify before continuing:

```bash
dig +short today.courieros.co        # must return your server IP
```

Do not proceed to step 2 until this returns an address.

---

## 2. Forge — attach the subdomain to the existing site

The tenant is the *same* Laravel application, not a second site. Do **not**
create a new Forge site.

1. Forge → your server → the `courieros.co` site → **Domains** (or *Site →
   Meta → Aliases* on older Forge UIs).
2. Add the alias: `*.courieros.co`
3. Save. Forge rewrites the nginx `server_name` to include the wildcard.

`ResolveTenant` reads `$request->getHost()`, strips the central domain from
`COURIEROS_CENTRAL_DOMAIN`, and looks the remaining label up against
`tenants.subdomain`. Once nginx accepts the host, `today` resolves on its own.

---

## 3. SSL — wildcard certificate

A per-host Let's Encrypt cert (HTTP-01) will not cover `*.courieros.co`.
Wildcards require a **DNS-01** challenge.

**Option A — Forge + Cloudflare DNS (recommended).**
Forge → Site → **SSL** → *LetsEncrypt* → tick the DNS challenge option and
supply a Cloudflare API token scoped to `Zone:DNS:Edit` for `courieros.co`.
Request the cert for both `courieros.co` and `*.courieros.co`. Forge handles
renewal.

**Option B — per-subdomain certs.**
If your DNS provider isn't supported for DNS-01, issue an ordinary HTTP-01
cert covering `courieros.co, www.courieros.co, today.courieros.co`. This works
today but needs reissuing for every new tenant, so treat it as a stopgap.

Verify:

```bash
curl -sI https://today.courieros.co | head -1     # expect HTTP/2 200
```

---

## 4. Environment

Compare the server's `.env` against `.env.production.example` in the repo
root. The values that matter for this task:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_NAME="CourierOS"                  # currently still "Ship'd JM" — the
                                      # browser tab on courieros.co reads
                                      # "Ship'd JM", fix this
COURIEROS_CENTRAL_DOMAIN=courieros.co
SESSION_DOMAIN=null                   # leave null — see below
SESSION_SECURE_COOKIE=true
```

**`SESSION_DOMAIN` must stay `null`.** A null value scopes the session cookie
to the exact host, keeping each tenant's session isolated. Setting it to
`.courieros.co` would share one session across every tenant on the platform.

After editing, Forge restarts PHP-FPM automatically. Then:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 5. Mail — this gates the whole signup flow

`/dashboard` sits behind Laravel's `verified` middleware. If mail doesn't
send, a new customer registers, never receives the verification link, and is
permanently stuck on the "verify your email" screen. To the client this looks
like the product is broken.

1. In Resend, add and verify the sending domain (`courieros.co`), which means
   publishing the SPF and DKIM records Resend gives you.
2. Confirm `RESEND_API_KEY` and `MAIL_FROM_ADDRESS` in the server `.env`.
3. Smoke test on the server:

```bash
php artisan tinker --execute="Mail::raw('CourierOS UAT mail test', fn(\$m) => \$m->to('admin@todayshippingja.com')->subject('CourierOS test'));"
```

Check it arrives (and check spam). Do not hand the environment to the client
until this passes.

---

## 6. Provision the tenant for UAT

The `today` tenant is seeded with demo data so dashboards, charts, and the
activity log have something to show. Its seeded accounts use `@today.test`
addresses, which are unroutable — the client's owner account needs a real one.

### 6a. Confirm the tenant exists and is active

```bash
php artisan tinker --execute="dump(App\Models\Tenant::where('subdomain','today')->first(['id','name','subdomain','status']));"
```

`status` must be `active`. `EnsureTenantSubscribed` treats an active tenant as
entitled, so the portal works without a live Stripe subscription — you do not
need to put the client through checkout for UAT.

If the tenant is missing, seed it:

```bash
php artisan db:seed --class=TodayShippingDataSeeder --force
```

### 6b. Give the client their owner login

`TodayShippingDataSeeder` now seeds `admin@todayshippingja.com` as the tenant
owner, already email-verified. On a freshly seeded environment the account
exists but has the seeder's throwaway password, so set a real one:

```bash
php artisan tenant:user today admin@todayshippingja.com \
    --name="Today Shipping Admin" \
    --role=owner
```

Omitting `--password` generates a strong one and prints it once — copy it
straight into whatever you use to hand credentials over. The command also
prints the sign-in URL and marks the address verified, so they are not stuck
behind the `verified` middleware waiting on mail that may not be configured
yet.

Add more of their staff the same way:

```bash
php artisan tenant:user today ops@todayshippingja.com --role=staff
php artisan tenant:user today manager@todayshippingja.com --role=admin
```

Roles: `owner` (full access including admin user management), `admin`
(operations + billing), `staff` (day-to-day package and pre-alert work).

> The old `tenant:reset --owner-email=...` flow still works, but it renames
> whichever owner it finds first and leaves you to set the password by hand
> through tinker. Prefer `tenant:user`.

### 6c. When they're ready for real data

Before they start entering genuine shipments, wipe the demo records:

```bash
php artisan tenant:reset today --force
```

This deletes packages, pre-alerts, package status histories, activity logs,
contact messages, customer accounts and profiles. It **keeps** the tenant, its
branding, warehouse address, shipping rates, and owner/admin/staff accounts.

---

## 7. Custom domain cutover

`ResolveTenant` checks `tenants.custom_domain` before it checks subdomains, so
switching later is a config change, not a code change.

1. Client points their domain at your server:
   `A  @  <server IP>` and `CNAME  www  todayshippingja.com`
2. Add the domain as an alias on the Forge site (step 2).
3. Issue a cert covering it.
4. Set the field:

```bash
php artisan tinker --execute="App\Models\Tenant::where('subdomain','today')->update(['custom_domain' => 'todayshippingja.com']);"
```

`today.courieros.co` keeps working alongside the custom domain.

---

## 8. Post-deploy verification

Run through this before sending the client their credentials.

- [ ] `dig +short today.courieros.co` returns the server IP
- [ ] `https://today.courieros.co` loads the TODAY Shipping landing page
      (cream background, red accents) — **not** the CourierOS marketing site
- [ ] Padlock is valid, no cert warning
- [ ] Logo and parcel images render (they now live at
      `/images/tenants/today-shipping/`, not the public root)
- [ ] `https://courieros.co` still shows the CourierOS platform site
- [ ] Register a brand-new customer on `today.courieros.co` → verification
      email arrives → email verifies → customer dashboard loads with a
      `TSL-` reference
- [ ] Log in as `admin@todayshippingja.com` → admin dashboard, customers,
      packages, pre-alerts, reports all load
- [ ] Create a pre-alert as the customer, see it in the admin queue
- [ ] Log out; confirm a session on `today.courieros.co` does not grant access
      on `courieros.co`
- [ ] `https://island.courieros.co` (the other seeded tenant) resolves
      separately and shows none of Today Shipping's data
- [ ] Browser tab on any Today Shipping page reads
      "… - Today Shipping & Logistics", never "CourierOS"
- [ ] Log in as `platform@courieros.co` on `courieros.co` → lands on
      `/platform`, **not** a customer dashboard, and Today Shipping appears on
      the tenant health board
- [ ] Suspend Today Shipping from `/platform/tenants`, load
      `today.courieros.co` → branded 403 page, not a bare "Forbidden".
      Reactivate afterwards.

---

## Known follow-ups

- `today-shipping-site/` still sits in the repo root. It's the superseded
  static prototype; the live page is
  `resources/views/tenants/today-shipping.blade.php`. Left in place by
  request — worth deleting or moving to `docs/prototypes/` later.
- `APP_NAME` on the production server is still `Ship'd JM`, which leaks into
  the browser tab title on `courieros.co`.
