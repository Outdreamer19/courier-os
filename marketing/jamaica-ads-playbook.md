# CourierOS Jamaica: ads playbook

Landing page: **https://courieros.co/jamaica** (funnel, minimal nav, every button goes to WhatsApp or signup).
Creatives: `marketing/ads/` (feed 1080x1350, story/reel 1080x1920, 22s reel MP4).

---

## 1. Before spending a dollar (one time setup)

1. **WhatsApp Business app** on the sales number (`COURIEROS_SALES_WHATSAPP`, default +44 7565 727032).
   - Profile: CourierOS logo (`public/branding/courieros-icon-180.png`), description "Courier software for Jamaican shop-and-ship businesses", link courieros.co/jamaica.
   - Greeting message: "Hi! Thanks for reaching out about CourierOS. Are you starting a courier business or already running one?"
   - Quick replies: `/price` (US$79 a month, founding setup US$149, no per-package fees), `/demo` (link to book a 15 minute screen share), `/live` (https://today.courieros.co).
2. **Meta Pixel**: Events Manager > Connect data > Web > Pixel. Put the ID in production `.env` as `META_PIXEL_ID`.
3. **Google tag**: create a GA4 property, put the measurement ID in `GOOGLE_TAG_ID` (G-XXXX). In Google Ads create a conversion action "WhatsApp lead" (website, manual) and put its send_to value in `GOOGLE_ADS_LEAD_SEND_TO` (AW-XXXX/label).
4. Founding offer is driven by env: `COURIEROS_FOUNDING_SETUP=149`, `COURIEROS_FOUNDING_SPOTS=10`, `COURIEROS_FOUNDING_SPOTS_LEFT=10`. **Drop SPOTS_LEFT by hand as people sign.** Set it to 0 and the offer disappears from the page.
5. After editing `.env` on Forge: `php artisan config:cache`.

Events the page fires (both Meta and Google): `ViewContent` on load, `Lead` on every WhatsApp click (with `placement`), `InitiateCheckout` on signup clicks. Optimise Meta on **Lead**.

---

## 2. Meta (Instagram, Facebook, WhatsApp)

### Campaign A: Click to WhatsApp (start here)
- Objective: **Engagement**, conversion location **Messaging apps > WhatsApp**.
- Location: Jamaica. Age 22 to 55. Advantage+ audience on, with suggestions:
  small business owners, entrepreneurship, online shopping, Amazon, SHEIN, Temu, freight forwarding, logistics, import/export.
- Placements: Instagram Reels, Stories, Feed; Facebook Feed. (Advantage+ placements is fine.)
- Budget: **US$10 a day for 7 days**, then move money to whatever ad set has the cheapest conversations.
- Ads:
  - Reel: `courieros-jamaica-reel.mp4`
  - Stories: `story-1` (pain), `story-2` (tracking), `story-5` (offer)
  - Feed: `feed-1`, `feed-2`, `feed-5`
- Pre-filled message: "Hi, I'm interested in CourierOS for my shipping business in Jamaica. Can you show me a demo?"

### Campaign B: Traffic to the landing page (week 2)
- Objective: **Leads**, conversion location Website, event **Lead**.
- Destination: `https://courieros.co/jamaica?utm_source=meta&utm_medium=paid&utm_campaign=jm-leads`
- Same audience and creatives. US$8 a day.

### Campaign C: Retargeting (once 500+ people have visited)
- Custom audience: website visitors last 30 days, excluding Lead.
- Creative: `feed-5` / `story-5` (the offer). US$3 a day.

### Primary text (pick 2 to 3 per ad set)
1. Still running your shipping business from WhatsApp and a spreadsheet? CourierOS gives you your own branded website, a customer portal with their Miami address, live tracking and WhatsApp updates. US$79 a month. Message us for a free demo.
2. Always wanted your own courier business? We build it with you. Branded site, Miami address for your customers, tracking and payments, set up on a call this week.
3. "Did my package reach yet?" "How much for 6 lb?" "How much I owe?" Let your website answer them. CourierOS, built for Jamaican shop-and-ship couriers.
4. Founding offer: the first 10 Jamaican couriers on CourierOS get setup for US$149 instead of US$349. Your brand, your customers, your prices.

Headlines: "Get a free demo on WhatsApp" / "Your own courier website" / "Founding setup US$149"

---

## 3. Google Search

- Campaign: Search only, location Jamaica (presence), language English. **US$6 a day**, Maximise clicks for week 1, then Maximise conversions on "WhatsApp lead".
- Final URL: `https://courieros.co/jamaica?utm_source=google&utm_medium=cpc&utm_campaign=jm-search`

### Keywords (phrase match)
"start a courier business", "start a shipping business jamaica", "how to start a courier business in jamaica", "shop and ship business", "courier software", "courier management software", "freight forwarding software", "courier website", "shipping company website", "package tracking system for business"

### Negative keywords
jobs, vacancy, salary, near me, dhl, fedex, ups, usps, track my package, tracking number, amazon tracking, customs duty calculator, free

### Responsive search ad
Headlines (30 chars max):
Start a Courier Business · Courier Software for Jamaica · Your Own Shipping Website · Give Customers a Miami Address · Tracking and WhatsApp Alerts · Run Your Shipping Business · US$79 a Month, No Package Fees · Founding Setup Only US$149 · We Set It Up With You · See a Free Demo Today · Built for Shop-and-Ship · Today Shipping Runs On It

Descriptions (90 chars max):
1. Your own branded courier website, customer portal, tracking and payments. Set up with you.
2. Customers get their own Miami address, send pre-alerts and track every package themselves.
3. US$79 a month. No per-package fees. Founding Jamaican couriers get setup for US$149.
4. Stop answering "did my package reach?" all day. WhatsApp updates go out automatically.

Sitelinks: See the price (`#offer`), Live example (https://today.courieros.co), WhatsApp us.

---

## 4. What good looks like

| Metric | Week 1 target |
| --- | --- |
| Meta cost per WhatsApp conversation | under US$3 |
| Landing page Lead rate (clicks to WhatsApp / visits) | 5% or more |
| Conversations to demo call | 1 in 4 |
| Demo to paid | 1 in 3 |

At those rates, US$300 of spend is roughly 100 conversations, 25 demos and 8 new couriers, which is US$632 MRR plus setup fees.

Review every Monday: kill any ad with 1,000+ impressions and no conversations, double the budget on the best one.
