# Jamaica launch tracker

Branch: `marketing/jamaica-launch` (not merged yet — review then merge or tell me to).

## Done (Claude)

- [x] GTM strategy: `CourierOS-Jamaica-GTM-Strategy.docx`
- [x] `/jamaica` landing page — segments, Full Package, founding cohort banner, FAQ (`resources/js/pages/central/Jamaica.vue`)
- [x] Wired into nav + footer + routes + controller
- [x] Smoke test: `tests/Feature/Central/MarketingJamaicaTest.php`
- [x] Outreach copy + target list: `marketing/jamaica-outreach.md`
- [x] `vue-tsc` type-check clean on all changed files

## Needs you (can't do these myself)

- [ ] **Stripe isn't connected** (`.env` keys are blank) — nobody can actually pay until this is set up. Biggest blocker.
- [ ] Run `npm run build` and `php artisan test` locally before deploying — this sandbox can't build (native binary mismatch) or run PHP at all
- [ ] Review the branch, merge it (or tell me to)
- [ ] Decide founding cohort terms: how many spots, how much off the $349 setup
- [ ] Get a real quote/numbers from Today Shipping — swap into the TODO comment in `Jamaica.vue`'s case study section
- [ ] Send the WhatsApp/IG/FB/email messages in `marketing/jamaica-outreach.md` — I can draft, not message people from your accounts
- [ ] Actually talk to Shipping Association of Jamaica + one creator about a partnership

## Next up if you want me to keep going

- Miami/Florida warehouse partner referral for Segment A
- Split-pay option for the setup fee (code change, once you confirm terms)
