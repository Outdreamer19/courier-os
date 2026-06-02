# SHIP DJM — Cursor Build Brief & Development Prompt

## 1. Project Summary

Build a fresh web application for **SHIP DJM**, a Jamaica-based shipping/logistics and package forwarding business.

The business helps customers in Jamaica shop from online merchants such as Amazon, Walmart, SHEIN, eBay, Fashion Nova, and other international stores. Customers ship their purchases to a warehouse in Florida. Customers then log into the SHIP DJM web app and submit a **pre-alert**, which is their invoice/receipt and shipment details so the admin knows what package they are expecting.

The platform must include:

1. Public marketing website
2. Customer portal
3. Restricted admin portal
4. Pre-alert management
5. Package management
6. Customer reference/account numbers
7. Warehouse/shipping address display
8. Rate calculator
9. Contact/support/feedback handling
10. Email and WhatsApp notification readiness
11. Online and in-person payment support foundation

This is a fresh build. Nothing is currently in place.

---

## 2. Confirmed Business Rules

Use these as the current MVP assumptions:

- Every customer should receive a unique customer/account reference number.
- The Florida warehouse address is unknown for now. Use a placeholder and make it editable by admin.
- The logo is not ready. Use a clean placeholder wordmark: **SHIP DJM**.
- The initial rate should be **JMD $500 per lb**.
- Future pricing may reduce slightly as package weight increases, so rate logic should support weight tiers later.
- For MVP, use one shipping rate/method only.
- Customers can pay **online** and **in person**.
- Do not build full payment gateway integration until the provider is confirmed.
- Add fields and UI placeholders for online payment, payment status, and manual payment recording.
- No delivery for now. Packages are **pickup only**.
- Customers should receive notifications by **email and WhatsApp**.
- WhatsApp integration can be placeholder/manual link for MVP unless a provider is selected later.
- Restricted/prohibited items are unknown for now. Create a placeholder Restricted Items Policy page and admin-editable content later if possible.

---

## 3. Recommended Tech Stack

Unless the existing project uses a different stack, build this using:

- Laravel 11 or latest Laravel version
- Vue 3
- Inertia.js
- TypeScript
- Tailwind CSS
- Laravel Breeze for authentication
- MySQL or MariaDB
- Spatie Laravel Permission or simple role-based access

If Cursor finds an existing codebase, inspect it first and follow the current project structure instead of forcing a new architecture.

---

## 4. User Types

### Guest User

A guest can access the public website without logging in.

They can:

- View homepage
- Learn how SHIP DJM works
- View shipping rates
- Use rate calculator
- View warehouse/shipping instructions
- Contact the business
- Read legal pages
- Register or log in

### Customer

A customer has a secure account.

They can:

- Log in and view dashboard
- View their unique customer reference number
- View their assigned Florida shipping address
- Submit pre-alerts
- Upload invoice/receipt for a purchase
- View package count and package statuses
- View pre-alert history
- View package history
- View payment status/amount due
- See pickup-only instructions
- Update profile details
- Submit support/contact/feedback messages
- Receive email/WhatsApp status notifications where configured

### Admin

Admins have restricted access to the admin portal.

They can:

- View admin dashboard
- Manage users/customers
- Create, view, edit, suspend, and delete customer accounts
- Generate or update customer reference numbers
- View all pre-alerts
- Update pre-alert status
- Create and manage packages
- Assign packages to customers
- Link packages to pre-alerts
- Update package status
- Add package weight and charges
- Record in-person payments
- Mark online payment status manually for MVP
- Manage rates
- Manage warehouse/shipping address information
- View support/contact submissions
- Trigger or prepare email/WhatsApp notifications

---

## 5. Public Website Pages

### Home Page

Purpose: Explain the service quickly and drive registration.

Recommended sections:

1. Hero
   - Headline: “Shop Online. Ship to Jamaica. Track Everything in One Place.”
   - Subtext: “SHIP DJM helps customers in Jamaica shop from stores like Amazon, Walmart, SHEIN and more, then ship packages through our Florida warehouse for pickup in Jamaica.”
   - Primary CTA: “Create Account”
   - Secondary CTA: “Calculate Shipping”

2. How It Works
   - Step 1: Create your SHIP DJM account
   - Step 2: Get your customer reference and Florida shipping address
   - Step 3: Shop from Amazon, Walmart, SHEIN, or another store
   - Step 4: Send the order to the Florida warehouse
   - Step 5: Submit your pre-alert with invoice/receipt
   - Step 6: Track package status and collect when ready

3. Why Choose SHIP DJM
   - Simple package tracking
   - Easy pre-alert process
   - Clear customer reference system
   - Pickup-ready notifications
   - Friendly customer support

4. Rate Calculator Preview
   - Show calculator or CTA to rates page
   - Default placeholder: JMD $500 per lb

5. Customer Portal Preview
   - Mock dashboard cards: customer reference, shipping address, active packages, pre-alerts, package status

6. FAQ Preview
   - What is a pre-alert?
   - What address do I ship to?
   - How are rates calculated?
   - When do I pay?
   - Where do I collect packages?

7. Final CTA
   - “Start shipping with SHIP DJM today.”

### About Page

Sections:

- Who SHIP DJM is
- Mission: making online shopping and package forwarding easier for Jamaicans
- What customers can expect
- Pickup-only notice for now
- Trust/reliability message
- Contact CTA

### Rates Page

Purpose: Let customers estimate shipping cost.

Current MVP rule:

- Base rate: **JMD $500 per lb**
- One shipping method only for now
- Pickup only
- Payment: online or in person

Rate calculator fields:

- Weight in lbs
- Optional declared value
- Estimated shipping cost
- Note that final charge may be confirmed by admin after package processing

Important implementation notes:

- Store rates in database or config, not hard-coded in Vue only.
- Support future weight tiers, for example:
  - 1–5 lb: $500 JMD/lb
  - 6–10 lb: placeholder reduced rate
  - 11+ lb: placeholder reduced rate
- For MVP, seed one active rate at $500 JMD/lb.

### Contact Page

Fields:

- Name
- Email
- Phone optional
- Subject
- Message

Submissions should be saved in the database and visible to admin.

### Legal Pages

Create placeholder pages for:

- Terms and Conditions
- Privacy Policy
- Shipping Policy
- Refund/Claims Policy
- Restricted Items Policy

Use professional placeholder text and add a visible note that final wording should be reviewed by the business owner/legal advisor.

---

## 6. Customer Portal

### Customer Dashboard

Show:

- Welcome message
- Customer reference/account number
- Assigned Florida warehouse address
- Copy-to-clipboard address buttons
- Number of active packages
- Number of submitted pre-alerts
- Amount currently due, if any
- Recent package statuses
- Recent pre-alerts
- CTA to submit new pre-alert
- Support/contact CTA

### My Shipping Address

Display:

- Customer full name
- Customer account/reference number
- Warehouse address line 1
- Address line 2
- City
- State
- ZIP
- Phone number if required
- Checkout instructions
- Copy buttons for full address and individual fields

Address should use placeholder content until the real Florida warehouse address is provided.

### Pre-Alerts

Customers submit a pre-alert before the package arrives.

Fields:

- Merchant/store name
- Order number
- Tracking number
- Courier/carrier: Amazon Logistics, USPS, UPS, FedEx, DHL, other
- Expected delivery date
- Item description
- Declared value
- Invoice/receipt upload
- Notes

Statuses:

- Submitted
- Under Review
- Matched to Package
- Issue Found
- Completed
- Cancelled

Customer can:

- View list of pre-alerts
- View details
- Upload invoice/receipt
- Edit only while status is Submitted or Under Review

### Packages

Customer can view packages assigned to them.

Package fields:

- Package ID/reference
- Tracking number
- Merchant/store
- Carrier
- Weight in lbs
- Amount due
- Payment status
- Package status
- Arrival date at Florida warehouse
- Shipped to Jamaica date
- Arrived in Jamaica date
- Ready for pickup date
- Pickup/completed date
- Customer-visible notes

Package statuses for pickup-only MVP:

- Awaiting Arrival
- Received at Florida Warehouse
- Processing
- In Transit to Jamaica
- Arrived in Jamaica
- Customs Processing
- Ready for Pickup
- Picked Up
- On Hold
- Cancelled

Avoid “Out for Delivery” and “Delivered” for now since there is no delivery yet.

### Payments

For MVP, do not integrate a live payment gateway until the provider is confirmed.

Add the foundation:

- amount_due on packages
- payment_status: unpaid, pending, paid, waived, refunded
- payment_method: online, in_person, manual, unknown
- paid_at nullable
- admin payment notes

Customer-facing UI:

- Show amount due
- Show payment status
- Show “Pay Online” button as disabled or placeholder until payment provider is configured
- Show instructions for paying in person on pickup

Admin UI:

- Record manual/in-person payment
- Mark package as paid
- Add payment notes

### Profile

Customer can update:

- Name
- Email, subject to verification if implemented
- Phone number
- Jamaica address
- Parish

Do not show delivery preferences for now because delivery is not offered.

### Support / Feedback

Customer can submit:

- General question
- Package issue
- Payment question
- Complaint
- Feedback/testimonial

Admin should be able to view and manage submissions.

---

## 7. Admin Portal

Admin portal must be restricted to admin users only.

### Admin Dashboard

Show:

- Total customers
- New customers this month
- Total pre-alerts
- Pre-alerts pending review
- Total packages
- Packages by status
- Packages ready for pickup
- Unpaid packages
- Recent packages
- Recent pre-alerts
- Recent contact/support messages

### User Management

Admin can:

- View customers
- Search by name, email, phone, customer reference
- Create customer
- Edit customer
- Suspend/activate customer
- Soft delete customer where appropriate
- View customer’s pre-alerts and packages

Customer fields:

- Name
- Email
- Phone
- Jamaica address
- Parish
- Customer reference/account number
- Role
- Status: active/suspended

Customer reference format suggestion:

- `DJM-000001`, `DJM-000002`, etc.

Make the format easy to change later.

### Pre-Alert Management

Admin can:

- View all pre-alerts
- Filter by status, customer, date, merchant, tracking number
- View invoice/receipt upload
- Update status
- Add internal notes
- Link pre-alert to package
- Notify customer by email/WhatsApp when status changes where configured

### Package Management

Admin can:

- Create package manually
- Assign package to customer
- Link package to pre-alert
- Update package details
- Add weight
- Auto-calculate amount due using active rate
- Override amount due if needed
- Update package status
- Record payment method/status
- Add warehouse/admin notes
- Add customer-visible notes
- Mark package ready for pickup
- Mark package picked up

### Warehouse / Shipping Address Management

Admin can manage:

- Florida warehouse address
- Address instructions
- Phone number to use at checkout
- Public shipping instructions
- Active/inactive status

Seed a placeholder warehouse address until the real one is known.

### Rate Management

Admin can manage:

- Rate name
- Rate per lb
- Minimum charge
- Optional handling fee
- Weight tier minimum
- Weight tier maximum
- Active/inactive status

For MVP seed:

- Name: Standard Air Shipping Placeholder
- Rate: JMD $500 per lb
- Method: Standard
- Minimum charge: JMD $500
- Active: true

### Contact / Feedback Inbox

Admin can:

- View contact submissions
- Mark as read/resolved
- Add internal notes

### Notification Readiness

Email:

- Use Laravel notifications/mailables where possible.
- Notify customer when package status changes.
- Notify customer when pre-alert status changes.
- Notify customer when package is ready for pickup.

WhatsApp:

- For MVP, add customer phone/WhatsApp field and a “Message on WhatsApp” admin action using a WhatsApp click-to-chat link.
- Leave provider-based automation for later.

---

## 8. Suggested Database Models

Use migrations, models, controllers, policies, and seeders where appropriate.

Suggested models:

- User
- CustomerProfile
- PreAlert
- Package
- WarehouseAddress
- ShippingRate
- ContactMessage
- SupportTicket or FeedbackMessage
- PackageStatusHistory
- PaymentRecord or PackagePayment
- SiteSetting optional

### User

Fields:

- id
- name
- email
- password
- role: admin/customer
- status: active/suspended
- email_verified_at

### CustomerProfile

Fields:

- user_id
- phone
- whatsapp_number nullable
- jamaica_address
- parish
- customer_reference unique

### PreAlert

Fields:

- user_id
- merchant_name
- order_number
- tracking_number
- carrier
- expected_delivery_date
- item_description
- declared_value
- invoice_path
- status
- admin_notes
- customer_notes

### Package

Fields:

- user_id
- pre_alert_id nullable
- package_reference unique
- tracking_number
- merchant_name
- carrier
- weight_lbs
- declared_value
- amount_due
- payment_status
- payment_method nullable
- paid_at nullable
- status
- received_at_warehouse_at
- shipped_to_jamaica_at
- arrived_in_jamaica_at
- ready_for_pickup_at
- picked_up_at
- admin_notes
- customer_visible_notes

### WarehouseAddress

Fields:

- name
- address_line_1
- address_line_2
- city
- state
- zip
- phone
- instructions
- is_active

### ShippingRate

Fields:

- name
- method
- currency default JMD
- rate_per_lb
- minimum_charge
- handling_fee nullable
- min_weight_lbs nullable
- max_weight_lbs nullable
- is_active

### ContactMessage

Fields:

- name
- email
- phone
- subject
- message
- status
- admin_notes

### PackageStatusHistory

Fields:

- package_id
- old_status
- new_status
- changed_by
- notes

### PaymentRecord / PackagePayment

Fields:

- package_id
- recorded_by nullable
- amount
- currency default JMD
- method: online, in_person, manual
- status: pending, paid, failed, refunded, waived
- reference nullable
- notes nullable
- paid_at nullable

---

## 9. Security and Access Rules

- Guests can only access public pages.
- Customers can only see their own profile, pre-alerts, packages, and support messages.
- Admins can see and manage all customer records.
- Use policies/middleware for authorization.
- Never trust customer-submitted `user_id` values from forms.
- Use authenticated user context instead.
- File uploads must be validated.
- Invoice uploads should allow PDF, JPG, PNG, and WEBP only.
- Add file size limits.
- Store invoice files securely.
- Avoid exposing internal admin notes to customers.
- Suspended customers should not be able to access the customer portal.

---

## 10. UI / Design Direction

Brand direction:

- Premium
- Reliable
- Clean
- Jamaican-inspired without looking childish
- Modern logistics/SaaS style

Colour direction:

- Black for premium sections and headers
- Gold for CTAs, borders, active states, highlights
- Green for success/status elements and subtle Jamaica connection
- White/neutral backgrounds for readability

Suggested UI style:

- Clean dark hero with gold/green accents
- Placeholder SHIP DJM wordmark until logo is ready
- White cards with soft shadows
- Rounded dashboard cards
- Clear status badges
- Mobile responsive layouts
- Copy-to-clipboard warehouse address UI
- Empty states for new users
- Search/filterable admin tables
- Package status timeline
- Minimal animations and polished hover states

---

## 11. MVP Build Priority

### Phase 1 — Foundation

- Public pages: Home, About, Rates, Contact, Terms, Privacy, Shipping Policy, Restricted Items Policy
- Auth: Register, Login, Logout
- Roles: admin/customer
- Customer reference generation
- Customer dashboard
- Admin dashboard shell
- Placeholder logo/wordmark

### Phase 2 — Customer Core

- Customer profile
- Shipping address display
- Submit pre-alert
- View pre-alerts
- Upload invoice/receipt
- View packages
- Show amount due/payment status

### Phase 3 — Admin Core

- Manage users
- Manage pre-alerts
- Manage packages
- Assign packages to customers
- Update package statuses
- Record package weight and charges
- Record manual/in-person payments
- View contact messages

### Phase 4 — Business Operations

- Rate calculator
- Admin rate management
- Warehouse address management
- Package status history
- Email notifications
- WhatsApp click-to-chat admin action

### Phase 5 — Polish

- Responsive design refinement
- Empty states
- Loading states
- Validation messages
- Admin filters
- Dashboard charts/cards
- Demo seed data

---

## 12. Remaining Open Questions

These should not block the MVP. Use sensible placeholders.

1. Exact Florida warehouse address
2. Final rate table and weight-tier rules
3. Payment provider for online payments
4. Pickup location/address in Jamaica
5. Restricted/prohibited items list
6. Final logo and brand assets
7. Official support email and phone/WhatsApp number
8. Final legal wording

---

# Cursor Prompt

You are working on a fresh web application for **SHIP DJM**, a Jamaica-based shipping/logistics and package forwarding business.

First, inspect the existing codebase carefully. Identify the framework, routing structure, auth setup, frontend framework, styling setup, database conventions, and existing models/controllers/components. Do not overwrite working code. Follow the existing project structure.

If the project is empty or not yet structured, build the MVP using Laravel, Vue 3, Inertia, TypeScript, Tailwind CSS, MySQL/MariaDB, and Laravel Breeze-style authentication.

## Business Context

SHIP DJM helps customers in Jamaica shop from online merchants like Amazon, Walmart, SHEIN, eBay, Fashion Nova, and other international stores. Customers ship their purchases to SHIP DJM’s Florida warehouse. Customers then log into shipdjm.com and submit pre-alerts, which are invoice/receipt submissions that tell the admin what package they are expecting. Admins manage users, pre-alerts, packages, rates, warehouse information, statuses, payments, and support messages.

This is a fresh app. Nothing is currently in place.

## Confirmed MVP Rules

- Every customer gets a unique customer reference/account number.
- Suggested customer reference format: `DJM-000001`.
- The Florida warehouse address is unknown. Seed a placeholder warehouse address and make it editable by admin.
- Logo is not ready. Use a clean text placeholder/wordmark: `SHIP DJM`.
- Base rate is JMD $500 per lb.
- Use one shipping method/rate for now.
- Build rate logic so future weight-tier discounts can be added later.
- Pickup only for now. No delivery functionality in MVP.
- Customers can pay online and in person, but the payment provider is not confirmed.
- Do not integrate Stripe/PayPal/Wipay/etc yet.
- Add payment fields and UI placeholders so integration can be added later.
- Admin must be able to manually record in-person payments and mark packages paid.
- Customer notifications should support email and WhatsApp readiness.
- Use Laravel notifications/mailables for email where possible.
- For WhatsApp MVP, add a click-to-chat admin action using the customer’s WhatsApp/phone number.
- Restricted items are unknown, so create a placeholder Restricted Items Policy page.

## Required Product Areas

Build three areas:

1. Public marketing website
2. Customer portal
3. Restricted admin portal

## Public Pages

Create:

- Home
- About
- Rates with rate calculator
- Contact
- Terms and Conditions
- Privacy Policy
- Shipping Policy
- Refund/Claims Policy
- Restricted Items Policy

### Homepage Content Direction

Use this positioning:

Headline: `Shop Online. Ship to Jamaica. Track Everything in One Place.`

Subtext: `SHIP DJM helps customers in Jamaica shop from stores like Amazon, Walmart, SHEIN and more, then ship packages through our Florida warehouse for pickup in Jamaica.`

Sections:

- Hero with Create Account and Calculate Shipping CTAs
- How It Works
- Why Choose SHIP DJM
- Rate Calculator Preview
- Customer Portal Preview
- FAQ Preview
- Final CTA

How It Works steps:

1. Create your SHIP DJM account
2. Get your customer reference and Florida shipping address
3. Shop from Amazon, Walmart, SHEIN, or another store
4. Send the order to the Florida warehouse
5. Submit your pre-alert with invoice/receipt
6. Track package status and collect when ready

## Customer Portal Requirements

Customer dashboard should show:

- Welcome message
- Customer reference/account number
- Assigned Florida warehouse address
- Copy-to-clipboard address buttons
- Active package count
- Pre-alert count
- Amount due, if any
- Recent package statuses
- Recent pre-alerts
- CTA to submit new pre-alert
- Support/contact CTA

Customer pages:

- Dashboard
- My Shipping Address
- Pre-alert list
- Pre-alert create
- Pre-alert show
- Pre-alert edit only when status allows
- Package list
- Package show
- Profile
- Support/feedback form

Pre-alert fields:

- merchant_name
- order_number
- tracking_number
- carrier
- expected_delivery_date
- item_description
- declared_value
- invoice_path
- customer_notes
- status

Pre-alert statuses:

- Submitted
- Under Review
- Matched to Package
- Issue Found
- Completed
- Cancelled

Package fields:

- package_reference
- tracking_number
- merchant_name
- carrier
- weight_lbs
- declared_value
- amount_due
- payment_status
- payment_method
- paid_at
- status
- received_at_warehouse_at
- shipped_to_jamaica_at
- arrived_in_jamaica_at
- ready_for_pickup_at
- picked_up_at
- customer_visible_notes

Package statuses for MVP:

- Awaiting Arrival
- Received at Florida Warehouse
- Processing
- In Transit to Jamaica
- Arrived in Jamaica
- Customs Processing
- Ready for Pickup
- Picked Up
- On Hold
- Cancelled

Payment statuses:

- unpaid
- pending
- paid
- waived
- refunded

Payment methods:

- online
- in_person
- manual
- unknown

Customer payment UI:

- Show amount due
- Show payment status
- Show disabled/placeholder Pay Online button until provider is configured
- Show in-person payment instructions for pickup

## Admin Portal Requirements

Admin dashboard should show:

- Total customers
- New customers this month
- Total pre-alerts
- Pending pre-alerts
- Total packages
- Packages by status
- Packages ready for pickup
- Unpaid packages
- Recent packages
- Recent pre-alerts
- Recent support/contact messages

Admin pages:

- Dashboard
- Customer/user CRUD
- Customer profile view with packages and pre-alerts
- Pre-alert management
- Package management
- Warehouse address management
- Rate management
- Contact/feedback inbox

Admin package actions:

- Create package manually
- Assign package to customer
- Link package to pre-alert
- Update package status
- Add package weight
- Auto-calculate amount due using active rate
- Override amount due if needed
- Record in-person/manual payment
- Mark paid/unpaid
- Add admin notes
- Add customer-visible notes
- Mark ready for pickup
- Mark picked up

Admin pre-alert actions:

- View all pre-alerts
- Filter/search
- View invoice/receipt upload
- Update status
- Add internal notes
- Link to package
- Notify customer by email/WhatsApp where configured

Rate management:

- Store active rate in database
- Seed one rate: JMD $500 per lb
- Support min/max weight fields for future tiered pricing
- Do not hard-code rate only in frontend

Warehouse address management:

- Seed placeholder Florida warehouse address
- Admin can edit address and instructions
- Customer shipping address page uses active warehouse address

## Suggested Models

Create migrations, models, controllers, policies, requests, resources, seeders as appropriate.

Models:

- User
- CustomerProfile
- PreAlert
- Package
- WarehouseAddress
- ShippingRate
- ContactMessage
- PackageStatusHistory
- PackagePayment
- SiteSetting optional

## Security Rules

- Guests can only access public pages.
- Customers can only access their own records.
- Admins can access all admin features.
- Use middleware/policies for authorization.
- Never trust customer-submitted user_id values.
- Use authenticated user context.
- Suspended customers cannot access portal features.
- Validate all form inputs server-side.
- Validate file uploads.
- Invoice uploads should allow PDF, JPG, PNG, and WEBP only.
- Add sensible upload size limits.
- Store uploads securely.
- Do not expose admin notes to customers.

## UI / Design Direction

Create a premium, modern logistics/SaaS interface using black, gold, green, and white space.

The design should feel:

- Clean
- Trustworthy
- Premium
- Jamaican-inspired without being childish
- Modern logistics/SaaS

Use:

- Dark hero sections
- White content cards
- Gold accent buttons
- Green status/success badges
- Clear dashboard cards
- Filterable admin tables
- Copy-to-clipboard buttons
- Empty states
- Responsive layouts
- Package status timelines
- Subtle hover effects
- Placeholder text logo/wordmark: `SHIP DJM`

## Implementation Order

1. Inspect the codebase and summarize what exists.
2. Create a safe implementation plan before modifying files.
3. Set up auth and roles.
4. Add migrations/models/seeders.
5. Build public website pages.
6. Build customer dashboard and portal pages.
7. Build pre-alert workflow.
8. Build package workflow.
9. Build admin portal.
10. Add rate calculator and rate management.
11. Add warehouse address management.
12. Add contact/support inbox.
13. Add payment foundation/manual payment recording.
14. Add email notification foundation.
15. Add WhatsApp click-to-chat admin action.
16. Polish UI, validation, empty states, loading states, and mobile responsiveness.
17. Provide a changed-files summary and manual test checklist.

## Demo Seed Data

Seed:

- One admin user
- One customer user
- One customer profile with customer reference `DJM-000001`
- One placeholder warehouse address
- One active shipping rate at JMD $500/lb
- A few sample pre-alerts
- A few sample packages with different statuses
- A few contact/support messages

## Important Instruction

Do not build fake live payment processing. Leave the payment gateway as a future integration until the business confirms the provider. Build the data structure, admin manual payment workflow, and customer-facing payment status UI only.
