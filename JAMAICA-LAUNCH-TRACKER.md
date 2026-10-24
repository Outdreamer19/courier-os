# CourierOS launch tracker

Updated 23 Oct 2026

## Blockers

- [ ] **One real signup end to end** on courieros.co with a real card, confirm tenant goes active and subdomain loads, then refund.

## First week

- [ ] Forge database backups on, and one test restore
- [ ] Error monitoring (Sentry or Flare)
- [ ] Queue the 4 customer notifications (welcome, package status, ready for pickup, pre alert) and start a Forge queue worker in the same deploy
- [ ] DMARC record on `_dmarc.courieros.co` (optional)

## Sales

- [ ] Decide founding cohort size for the US$99 setup
- [ ] Send messages in `marketing/jamaica-outreach.md`
- [ ] Post the network story (`marketing/ads/network-story.png`)
- [ ] Talk to Shipping Association of Jamaica and one creator about a partnership

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
