# Wholesale Eco Products — Laravel implementation handover

**Prepared for:** Sakib / Mento Software development team  
**Scope:** UK business-to-business wholesale packaging commerce.  
**Status:** Frontend design demonstrator only; no production backend exists in this package.

## 1. What has already been supplied

- `index.html`: premium homepage
- `catalogue.html`: Kite-inspired wholesale quick-order table
- `checkout.html`: one-page checkout form and basket totals (demonstration, no payment)
- `customer-portal.html`: business dashboard, order history, reordering, saved lists, printable sample records, addresses, profile
- `admin-portal.html`: overview, orders, catalogue, stock, bulk pricing, customer accounts, reports, settings and access-control concept
- `assets/style.css`, `assets/app.js`, `assets/portal.css`, `assets/portal.js`, `assets/products.svg`, `assets/wholesale-eco-logo.png`

**Caution:** Demo order values, VAT rate, stock, tier pricing, free delivery, account data and financial KPIs are illustrative. Frontend localStorage is not trusted storage and must not become the source of truth.

## 2. What we need from Sakib to start the Laravel integration

### Existing work and technical architecture

1. The **complete current Laravel project ZIP or private Git repository URL/access** (if already started), including `composer.json`, `package.json`, `routes/`, `app/`, `resources/`, `database/migrations/`, tests and documentation. Send an `.env.example` file, **not `.env` containing real secrets**.
2. Installed **Laravel and PHP versions**, current frontend framework (Blade/Livewire/Vue/Inertia), build tool, database version, PHP extensions and current authentication package.
3. Database schema, migrations, relationships and sample sanitized seed data, if present. Clearly identify any live tables and existing customer/order data that must be preserved.
4. List of already implemented and pending modules, API endpoints, outstanding bugs and blockers.
5. Hosting architecture: dev/staging/production environments, deployment approach, queue worker, scheduler, object/file storage, SSL and backup strategy. Share infrastructure configuration **without passwords**.
6. Current `README` or developer setup steps, automated tests and deployment scripts.

### Business decisions needed from client (Sakib should collect)

1. Confirm final domain, business legal name, company number and exact logo assets.
2. Full product list: SKU, category, descriptions, sizes, material, pack units, pallet conversions, product images, and each SKU's available stock. A structured spreadsheet/CSV is ideal.
3. Wholesale pricing rules: base selling price, pence-per-unit display, quantities per pack, pack ranges, Mix & Match eligible groups, pallet rates, customer-specific discounts, minimum quantities and rounding order.
4. Business customer workflow: **public wholesale pricing + guest checkout**, account-required checkout, or approved trade-only prices? Decide whether company verification is mandatory and whether customers may have multiple user accounts/branches.
5. VAT details: seller VAT registration, per-SKU VAT tax class, invoice requirements, exemptions, shipping tax handling, rounding and credit notes. Confirm with UK accountant.
6. Delivery rules: UK zones/postcodes, weight/volume/pallet constraints, standard/express rates, free-delivery thresholds, cut-off times, collection options and courier partners.
7. Payment methods: Stripe card + Apple Pay/Google Pay (subject to eligibility), PayPal if needed, bank transfer and credit terms only if approved. Set up payment-provider **test** access through a secure secrets manager.
8. Fulfilment: order states, picking/packing workflow, split shipments, backorders, returns, stock reservation timing and cancellation/refund policy.
9. Email/SMS templates, customer service inbox, marketing consent policy, UK GDPR privacy/data retention requirements, T&Cs and contact information.
10. Delivery timeline, deployment target, acceptance criteria and who signs off each module.

## 3. Recommended Laravel stack

- Supported current Laravel / PHP release selected after inspecting Sakib's repository; do not upgrade a partially completed project blindly.
- Blade + Livewire + Alpine.js and Tailwind CSS for server-driven commerce, unless an existing Vue/Inertia setup is already established.
- MySQL or PostgreSQL; Eloquent ORM with foreign keys, indexes and transactional checkout logic.
- Laravel authentication and policies; Spatie permission library or equivalent for Super Admin, Sales, Warehouse, Finance, Customer Service, Business Owner, Business Staff.
- Stripe Checkout / PaymentIntents with **server-verified idempotent webhooks**; do not trust the browser response to confirm paid orders.
- Laravel queues, jobs and scheduler for email, invoices, stock alerts and asynchronous operations.
- Private file storage for documents, object storage/CDN for public product images.
- Automated feature and unit tests for price tiers, stock concurrency, customer isolation, taxes, shipping and payment processing.

## 4. Data model (minimum)

- `users`, `businesses`, `business_user`, `business_addresses`, `roles`, `permissions`
- `categories`, `products`, `product_variants`, `product_images`, `product_attributes`, `product_compatibilities`
- `uoms`, `variant_pack_conversions`, `price_lists`, `price_tiers`, `mix_match_groups`, `mix_match_group_variants`, `customer_price_overrides`
- `stock_locations`, `stock_balances`, `stock_movements`, `stock_reservations`
- `carts`, `cart_items`, `saved_lists`, `saved_list_items`
- `orders`, `order_items` (immutable product/price/tax snapshots), `order_addresses`, `order_status_history`
- `payments`, `payment_webhook_events`, `refunds`, `invoices`, `credit_notes`
- `shipments`, `shipment_items`, `shipping_zones`, `shipping_rates`
- `coupons`, `cms_pages`, `banners`, `settings`, `audit_logs`

Keep **order fulfilment status**, **payment status** and **stock allocation status** independent.

## 5. Bulk pricing / ordering rules that require special attention

1. Store canonical stock in one carefully chosen base UOM (e.g. pieces) and maintain **exact conversion factors** to packs and pallets for each variant. Define whether customers enter number of pieces or number of packs; never silently confuse them.
2. Store currency as GBP integer minor units (pence) for settled line totals. For fractional pence-per-unit display, use a defined decimal precision and **documented final rounding policy**.
3. Tier selection may be based on SKU quantity, product family, eligible Mix & Match group or overall packs; the business owner must decide. Do not apply group discounts to unrelated products by default.
4. Calculate all applicable tier thresholds, discounts, shipping and VAT **server side** at checkout and again when confirming payment as appropriate.
5. Never use customer-controlled HTML/JS/localStorage as an authoritative price, customer ID, or stock figure.
6. Changes to catalogue prices do not alter historical order snapshots.
7. A reorder must use historic line items and quantities but **current valid SKU, current price, current stock and current delivery terms**. Show any changed/unavailable items before checkout.

## 6. Phase 1 feature acceptance checklist

### Storefront
- [ ] Responsive homepage, accessible navigation, category and filtered search pages
- [ ] Product photos, variant specification, matching lids/containers, pack quantity and pence-per-unit display
- [ ] Quick-order grid with pack/pallet conversions and accurate volume discount highlighting
- [ ] Basket persistent for authenticated users; guest basket merges on login
- [ ] Checkout: business details, guest/registered options, separate shipping/billing, delivery service, tax, totals, payment and confirmation

### Customer portal
- [ ] Secure login/password reset/email verification, tenant-safe business accounts
- [ ] Business profile and multiple delivery locations
- [ ] Order history, detailed orders, tracking and document access
- [ ] One-click reorder with live repricing and stock checks
- [ ] Saved lists/favourites and downloadable valid VAT invoices
- [ ] Customer support enquiry / contact route

### Admin portal
- [ ] Authenticated RBAC and audit history for sensitive actions
- [ ] KPI dashboard based on actual orders, payments and stock
- [ ] Products, SKUs, variants, images, CSV import/export
- [ ] Inventory transactions, reorder thresholds and stock reservation
- [ ] Price lists, tier editor, Mix & Match groups and customer-specific overrides (if included)
- [ ] Order queue, picking/packing, shipments, returns and refunds
- [ ] Trade customer onboarding, account settings, company users and negotiated terms
- [ ] Invoices, reports, delivery tariffs, configurable content, alerts and payment/webhook review

### Reliability / security
- [ ] Test checkout pricing, taxes and unit conversions at tier boundaries
- [ ] Simulate concurrent orders for the last stock unit to prevent overselling
- [ ] Test duplicate, delayed and out-of-order Stripe webhooks
- [ ] Test order cancellations and partial/full refunds
- [ ] Test that business A cannot access business B's invoices, orders or addresses
- [ ] Backups and tested restore procedure, HTTPS, CSRF, validation and rate limiting
- [ ] WCAG-minded keyboard accessibility, mobile responsiveness and basic SEO metadata

## 7. Integration strategy (recommended sequence)

**Step A — Audit & decisions (before coding):** Sakib provides existing repository, versions, migrations, sample data, implemented modules and business-rule decisions. We map demo UI components to existing Laravel routes and determine what can be reused.  
**Step B — Backend foundations:** Authentication, business tenancy, catalogues, SKU units/conversions, inventory ledger, price calculation service, address and delivery models.  
**Step C — Commerce:** Real cart, validated checkout, Stripe test mode, secure webhook handling, order confirmation, emails and invoices.  
**Step D — Customer portal:** Business accounts, permissions, reorder using live validation, saved lists, addresses and order history.  
**Step E — Admin portal:** Products, stock, order fulfilment, prices, customer management, reports, settings.  
**Step F — QA & staging:** Automated tests, sandbox payments, role/security testing, UX and performance review, backups, deployment checklist.  
**Step G — Production:** Signoff, configuration/secrets in secure hosting, monitored launch and rollback plan.

## 8. Exact first request to send to Sakib

> Please send me your current complete Laravel project (ZIP or Git repository), the Laravel/PHP/database versions, database migrations, sanitized sample data, frontend framework details, README, any implemented API endpoints, and a clear list of completed/pending modules. Include `.env.example` only, not live passwords or API keys. Also confirm whether you want to integrate our five approved HTML pages into the current Laravel codebase or start with a fresh Laravel project. Once I have the repository, I can review its architecture and prepare a file-by-file integration and development plan.

---

**Ownership / boundaries:** This package is frontend code and handover instructions, **not** a ready-to-deploy Laravel app. Actual backend work cannot be reviewed or completed accurately until the Laravel repository and business decisions are available.
