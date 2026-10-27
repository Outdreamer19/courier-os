# CourierOS launch tracker

Updated 27 Oct 2026

## Blockers

- [ ] **One real signup end to end** on courieros.co with a real card, confirm tenant goes active and subdomain loads, then refund.

## First week

- [ ] **Turn on the Scheduler for courieros.co in Forge.** It is off, so `demo:reset` (nightly) and the hourly InvoiceFeed payment sync have never run in production
- [ ] **Add a queue worker for courieros.co** (`queue:work database`). None exists, so lead and support notifications already sit unsent in the `jobs` table
- [ ] DMARC: record filled in on Cloudflare (`_dmarc`, `v=DMARC1; p=none;`), needs Save

- [ ] Database backups: Forge needs the Business plan for this. Either upgrade, or add free backups to R2 with spatie/laravel-backup. Then one test restore
- [ ] Error monitoring: code ready on `feature/error-monitoring` (Sentry). Merge, create a free sentry.io Laravel project, set `SENTRY_LARAVEL_DSN` in Forge, run `php artisan sentry:test`
- [ ] Queue the 4 customer notifications: code ready on `feature/queue-customer-notifications`. Add a Forge queue worker (connection `database`) and `php artisan queue:restart` in the deploy script BEFORE merging
- [ ] DMARC record on `_dmarc.courieros.co` (optional)

## Sales

- [ ] Decide founding cohort size for the US$99 setup
- [ ] Send messages in `marketing/jamaica-outreach.md` (copy refreshed 27 Oct: pricing, contact details, follow up)
- [ ] Post the network story (`marketing/ads/network-story.png`)
- [ ] Talk to Shipping Association of Jamaica and one creator about a partnership

## Notes

- courieros.com is registered by someone else (IONOS, since Dec 2020, expires 22 Dec 2026). A redirect isn't possible; watch for it dropping or make an offer.
- Higgsfield voice: ToS do not restrict commercial use of outputs and there is no separate licence. Use a paid plan for watermark free downloads. IP indemnity is Enterprise only.
- Copy now says Florida, not Miami (`copy/florida-not-miami`).

## Done

- [x] Stripe live mode in production: live keys, live price IDs and live webhook secret in the Forge `.env` (24 Oct 2026)

- [x] Stripe checkout work merged to main (PR #24)
- [x] New UK Stripe account "CourierOS" (acct_1UTr2LIF74BraqWE) under the Quantara Digital org. Test mode: Starter US$63/mo, Setup Fee US$149, Founding Setup Fee US$99, webhook to `https://courieros.co/stripe/webhook`

- [x] `/jamaica` landing page, nav, routes, smoke test
- [x] GTM strategy doc and outreach copy
- [x] Wildcard subdomains live (any `*.courieros.co` reaches the app)
- [x] Resend verified, mail delivering from `noreply@mail.courieros.co`
- [x] Free access hole closed (tenants need a subscription or trial)
- [x] Public demo at demo.courieros.co (Blue Mahoe Express)
- [x] Prices lowered to US$63/mo + US$149 setup (US$99 founding)
- [x] Branded error pages, white label leaks fixed, proxy trust, reserved subdomains
