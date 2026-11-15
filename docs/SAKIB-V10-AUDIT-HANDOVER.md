# Wholesale Eco Products — V10 storefront fixes and operational audit
**Date:** 15 November 2026. **Source:** user-uploaded `wholesale-eco-main (4).zip`, not a live-server export. This is a staging candidate, NOT approved for live sales.

## Changes made directly to uploaded Laravel source

1. **Full-bleed homepage hero.** Added `public/css/v10-review.css` with a viewport-wide outer hero, centred content, responsive handling, and an explicit new CSS asset version so browsers can fetch the changed file. No other homepage sections were removed.
2. **Category product presentation.** Removed redundant attribute strings from the first product column and changed its heading to `Product`. Category overview shows its first product's range description and relevant specifications above the table. Capacity, dimensions and material remain in their own table columns for easy SKU comparison, with the full detail page linked per row.
3. **Discount-tier highlighting.** Updated the quantity steppers to emit input events immediately. Each row highlights its own 1–3, 4–9, 10–19, 20+ tier based on pack-equivalent quantity. To let users compare higher tiers on out-of-stock items, quantity editing now works as a *price preview* even when stock is zero, but the **Add** button remains disabled if the requested quantity is greater than available stock. Both server cart and checkout still validate stock and actual pricing. **Important:** the four sample imported kraft-bowl SKUs have no quantity price-tier records; therefore prices stay the same across all four columns until the merchant configures discounts. Highlighting does not create a discount.
4. **Administration navigation.** Explicit `Manage Categories` link added under Product Catalogue, and separate `Admin Users` and `Roles & Permissions` links added. The roles editor is deliberately only available to full administrators, because it grants security permissions. The underlying controllers, roles migration and views were already present; this change improves discoverability, not a new role system.
5. **Footer.** Reduced and balanced brand column; responsive two-column footer on smaller screens. The customer-facing contact details are read from Business Setup. Known sample details (`support@example.test`, `020 7946 0000`, `1 Example Street`) are suppressed rather than advertised as real. **Sakib must confirm actual values** in live Business Setup; these changes DO NOT modify live database values.
6. **Supplier photos restored.** Extracted six genuine packaging photographs from the user-provided two-page range sheet and bundled them into `public/images/catalogue` and `storage/app/public/catalogue`. The corresponding default catalogue SKUs now prefer shipped static assets if available, and merchant-uploaded images still use public storage.
7. **Contact form guard.** A test-domain support email is not accepted as a deliverable contact destination.

## Admin URLs / menus

- **Categories:** `/admin/categories` → create category, slug, optional parent; edit using each row's link. Set category parent *Food Packaging* to nest kraft bowls etc.
- **Administrator users:** `/admin/team` → create user, set staff or full administrator; choose role for staff.
- **Editable role groups:** `/admin/roles` → superadministrator only. Assign roles from `/admin/team`. A staff account with limited permissions may not see the full management navigation.
- **Stock and price tiers:** `/admin/variants` and the relevant Product Variants editor. Quantity tier records must be set for discounts to change actual totals.
- **Images:** Product Catalogue → open product → Images.

## Operational readiness — BLOCKERS (do not go live without resolution)

**P0 — VAT and invoices.** `PricingService` calculates line-item VAT but explicitly omits delivery VAT. VAT invoicing is labelled DRAFT and lacks verified immutable merchant data / sequential invoice numbering. Tax treatment and actual invoice output require qualified business review and end-to-end tests.

**P0 — Stock & payment lifecycle.** `OrderService` decrements stock at pending checkout creation; it releases on failed session creation or on expiration/async failure webhook. Validate stale/abandoned payment and delayed webhook races; confirm no permanent stock holds, overselling, double processing or inconsistent refunds. Verify actual Stripe live and test credentials, signed webhooks, receipt/payment statuses, and reconcile totals.

**P0 — Fake business data and catalogue stock.** This uploaded code contains `DemoStoreSettingsSeeder` using demo telephone, email and address, and contains demo-user seeders. Users may have run these previously. Confirm the deployed database contains real merchant settings and remove demo accounts. Supplier-imported SKUs start at stock **zero** intentionally. Enter verified stock only after physical confirmation. Do not run `demo:reset` on production: it is destructive.

**P1 — Delivery configuration.** Admin Shipping Zones editor explicitly says its rules are not connected to checkout. Checkout uses `.env` flat/free shipping values, not postcode rules. Test and integrate zones if required, including applicable delivery VAT.

**P1 — Advanced wholesale pricing.** Mix & Match, pallet-specific discounts and fully dynamic case/pallet tier rules are not implemented. Pack/pallet conversion uses existing case tiers, not separate pallet rates.

**P1 — Roles & security.** Server-side permission middleware exists, but needs manual tests for all route groups, changes to permissions, staff account creation, admin access control, password reset, rate limiting, and removal/deactivation scenarios.

**P1 — Operational compliance.** Verify published UK company and VAT details, privacy and cookie-consent integration (before non-essential analytics), product material claims, delivery/returns terms, communication templates and external integrations.

**P1 — Documentation and backup.** Confirm database backups/restoration, PHP/composer dependencies, mail queues, scheduled tasks, SSL, logs, staging-vs-production secrets and performance tests.

## Deployment and verification instructions

1. Back up current production files, uploaded images and database. **Compare** V10 against Sakib's most recent server changes before deploying. Use staging first.
2. Install and run dependencies on staging: `composer install --no-dev --optimize-autoloader`, `php artisan migrate --force`, `php artisan storage:link` (only if necessary), `php artisan optimize:clear`.
3. Verify assets `public/images/catalogue/*.jpg`, `public/css/v10-review.css`, `public/js/kite-product-v4.js` are accessible; purge CDN cache after deployment. Confirm `APP_ENV=production`, `APP_DEBUG=false`, HTTPS.
4. Test home hero at desktop 1440px, tablet and mobile, including no horizontal scroll.
5. Test `/shop?category=kraft-bowls-and-lids` and `/products/kraft-bowls-and-pp-lids`: pictures, four variants, descriptions, 1/4/10/20 quantities, stock warnings, pack/pallet toggle, case/unit display. Configure real tiers in staging and verify checkout pricing matches highlighted tier; 1/4/10/20 are **thresholds in cases, not units**.
6. As a full administrator, verify `/admin/categories`, `/admin/team`, `/admin/roles`; as a staff member confirm permissions correctly limit each action.
7. Replace **all demonstration business details** in Business Setup. Test footer and contact form with configured mail delivery.
8. Perform Stripe test-mode success, decline, duplicate/reordered webhook, expired session, cancellation, refund, stock-reservation and concurrent checkout tests before taking real orders.
9. Run `php artisan test` and screenshot regression tests in staging. This code package was checked only for static integrity in the creation environment; no Composer/vendor or staging DB were available here.

## Non-negotiable status

**V10 is suitable for staging review only. NOT production-approved.** Source-level checks cannot certify a deployed payment system, permission model or compliant UK VAT invoicing.
