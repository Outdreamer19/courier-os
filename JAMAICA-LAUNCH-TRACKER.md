# CourierOS launch tracker

Updated 28 Oct 2026

## Blockers

- [ ] **One real signup end to end** on courieros.co with a real card, confirm tenant goes active and subdomain loads, then refund.

## Before ads go out

- [ ] Set `META_PIXEL_ID` in production (Forge env)
- [ ] SEO: no SSR on marketing pages, and the page template has no meta description or social preview tags (sitemap exists)

## Sales

- [ ] Decide founding cohort size for the US$99 setup
- [ ] Send messages in `marketing/jamaica-outreach.md` (copy refreshed 27 Oct: pricing, contact details, follow up)
- [ ] Post the network story (`marketing/ads/network-story.png`)
- [ ] Talk to Shipping Association of Jamaica and one creator about a partnership

## Notes

- courieros.com is registered by someone else (IONOS, since Dec 2020, expires 22 Dec 2026). A redirect isn't possible; watch for it dropping or make an offer. Ads use courieros.co.
- Higgsfield voice: terms allow commercial use of outputs on any plan, no separate licence. Paid plans give watermark free downloads. IP indemnity is Enterprise only.
- Nightwatch agent for courieros.co listens on port 2408 (`NIGHTWATCH_INGEST_URI=127.0.0.1:2408`) because something else on the server holds 2407.

## Done

- [x] Test restore of an R2 backup into a scratch database: all 28 tables, real data (28 Oct 2026)
- [x] Nightly database backups to Cloudflare R2 bucket `courieros-backups` at 02:00 UTC, failure emails only, first backup verified (27 Oct 2026, PR #28)
- [x] Error monitoring with Laravel Nightwatch (CourierOS, Production, EU), agent running in Forge (27 Oct 2026, PR #32)
- [x] Customer notifications queued, tenant carried into jobs, Forge "CourierOS queue worker" running (27 Oct 2026, PR #30)
- [x] Forge Scheduler enabled for courieros.co, so demo reset, InvoiceFeed payment sync and backups run (27 Oct 2026)
- [x] DMARC record `_dmarc.courieros.co` = `v=DMARC1; p=none;` (27 Oct 2026)
- [x] Site copy says Florida, not Miami (27 Oct 2026, PR #31)
- [x] Outreach copy refreshed (27 Oct 2026, PR #27)
- [x] Stripe live mode in production: live keys, live price IDs and live webhook secret in the Forge `.env` (24 Oct 2026)
- [x] Stripe checkout work merged to main (PR #24)
- [x] New UK Stripe account "CourierOS" (acct_1UTr2LIF74BraqWE) under the Quantara Digital org
- [x] `/jamaica` landing page, nav, routes, smoke test
- [x] GTM strategy doc and outreach copy
- [x] Wildcard subdomains live (any `*.courieros.co` reaches the app)
- [x] Resend verified, mail delivering from `noreply@mail.courieros.co`
- [x] Free access hole closed (tenants need a subscription or trial)
- [x] Public demo at demo.courieros.co (Blue Mahoe Express)
- [x] Prices lowered to US$63/mo + US$149 setup (US$99 founding)
- [x] Branded error pages, white label leaks fixed, proxy trust, reserved subdomains
- [x] Motion ad set built (30s, 15s, three hook tests)
