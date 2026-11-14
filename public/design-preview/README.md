# Packhaus — premium wholesale packaging website prototype

Open `index.html` in your browser to preview the homepage. Navigate to `catalogue.html` for the Kite-style wholesale ordering table, and `checkout.html` for the responsive one-page checkout.

## What's included
- `index.html` — responsive premium homepage
- `catalogue.html` — working pack quantity controls, separate product group pricing tiers, live basket
- `checkout.html` — delivery details and recalculated order totals (demo only)
- `assets/style.css` — design system and responsive styles
- `assets/app.js` — frontend interactions and localStorage basket
- `assets/products.svg` — original vector illustrations

## IMPORTANT — demo limitations
This is a **front-end concept**, not a working commerce backend. No payment is collected, no order is sent to a server, and the customer form data is not submitted. Product prices, delivery terms, branding and VAT are fictional demonstration content. VAT is uniformly 20% in this demo, which may not apply to every real product. Checkout submission only shows a confirmation explaining these limitations.

To launch in production, connect pages to Laravel authentication, products and stock database, server-side pricing, Stripe Checkout / verified webhooks, VAT rules, address validation, email delivery, and order creation. Do not treat browser localStorage as an authoritative order record.

Product artwork is original SVG, and no images are required from third-party CDNs. Fonts fall back to system fonts without internet access.


## Added: Premium customer and admin portals
- `customer-portal.html` — customer overview, past orders, quick reorder linked to the existing demo cart, saved basket lists, sample invoice print views, addresses, account profile.
- `admin-portal.html` — executive dashboard, orders and status changes, catalogue, SKU stock adjustments, tier price editor, customer list, reports, store settings, team access preview.
- `assets/portal.css` and `assets/portal.js` — premium responsive styling and interactive demo logic.
- `assets/wholesale-eco-logo.png` — supplied updated branding asset (not used to replace legacy storefront wordmark by default).

Open `customer-portal.html` and `admin-portal.html` directly. The home/catalogue/checkout pages now have links to both portals. Customer quick reorder adds items to the SAME localStorage basket consumed by checkout. Admin sample status, stock, lists and settings persist locally in the browser.

**Important:** All users, order totals, stock and statuses shown in portals are fictional, and none are secured, authenticated, or synced to a server. Admin pricing edits only change the admin's local preview; they intentionally DO NOT alter authoritative shared catalogue/checkout prices. A real backend must implement secure authentication, RBAC, tenancy isolation, stock allocation, pricing and discounts, Stripe payment webhooks, VAT, invoices, shipping, audit history, and live database persistence.

See `SAKIB-LARAVEL-HANDOVER.md` for the production implementation plan and requirements checklist.
