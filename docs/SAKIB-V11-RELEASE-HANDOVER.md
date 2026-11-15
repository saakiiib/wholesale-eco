# Wholesale Eco Products — Laravel V11 developer handover

## Source baseline
V11 is based on **wholesale-eco-laravel-v10-review-fixes.zip**. Before merging with the actual server source, compare files and preserve newer changes, uploads, database records and `.env`. Never overwrite the live database or .env from this archive. No vendor folder or production secrets are included.

## Changes and paths

1. **Product listing layout** — `public/css/v11-catalogue.css` gives the product-image/name cell a fixed usable width and stops letter-by-letter wrapping. Tables remain horizontally scrollable, particularly on smaller screens; test both category range and individual product pages.
2. **Catalogue ordering** — migration `2026_11_15_000007` adds `products.sort_order`; also normalises existing category and product display positions to multiples of 10, preserving pre-migration category ordering and alphabetical product ordering. Admin categories/products now include up/down actions (`admin.categories.move`, `admin.products.move`) and an editable position on the form. Moves act within the same parent category (for categories) or the same category (for products). Public top navigation now uses sorted active root categories; child categories remain in sidebars. **Check category navigation after the migration.**
3. **Logout** — an authenticated POST form on the portal sidebar uses the existing logout route and Laravel CSRF protection.
4. **Admin profile edits** — admin team directory includes an Edit details link. Full administrators can edit another admin/staff member's name, email, phone and password. Passwords use hashed casting. Self-password changes require the current password. New migration `2026_11_15_000008` adds users.phone. Do not share new passwords in plain text via email.
5. **VAT display** — the top header displays Ex VAT / Inc VAT and remembers the visitor's preference locally in browser storage. Javascript recalculates only displayed catalogue/homepage price values using each SKU's VAT rate, and leaves Laravel's quote, VAT amounts and Stripe payment unaffected. The final checkout separately discloses VAT. Browser testing must cover the case/unit toggle and all SKUs with 0%, 5%, 20% VAT.
6. **Coupons** — `Coupon`, `AdminCouponsController`, coupon admin view, cart apply/remove endpoint and `PricingService`/`OrderService`/Stripe webhook integration. Admin can create/edit/disable single-use or multi-use fixed GBP or percentage discounts, minimum subtotals, limits, start/end date and max discount. Price and VAT are recomputed server side. Coupon usage is reserved transactionally at pending-order creation, redeemed on verified paid Stripe webhook, and released on payment failure / Stripe session expiry. An expired session with a missing webhook may leave a stale reservation and needs reconciliation with Stripe before production. Coupon refunds don't currently restore single-use eligibility automatically. **Staging-test all cases.**

## Deployment steps

1. Full code and DB backup, staging branch, review diff against any newer Sakib changes.
2. `composer install --no-dev --optimize-autoloader` (for staging use dev dependencies if running tests).
3. `php artisan migrate --force` on staging; verify two new migrations and existing category/product positions.
4. `php artisan optimize:clear`, build/cache as appropriate to the existing setup.
5. Run `php artisan test` on staging, especially `V11CommerceTest`, all V9/V10 tests and authentication tests.
6. Validate all screens in Chrome desktop, Chrome Android and Safari iPhone. Review `/admin/team`, `/admin/categories`, `/admin/products`, `/admin/coupons`, `/shop`, `/basket`, `/checkout`.
7. Run Stripe TEST-mode payments: valid/invalid codes; fixed and percentage discounts; 1-use coupon in two simultaneous carts; successful webhook; asynchronous success; failed/expired sessions; duplicate event replay; VAT calculation; coupon disabled after entry; refund treatment; pending reservation count consistency.
8. Verify coupon discounts are shown correctly in basket, checkout, order details and printed draft statement. **Do not issue formal VAT invoices until delivery VAT and invoice sequencing have been reviewed**.
9. Migrate to production only after testing, formal client sign-off and rollback preparedness.

## Operational blockers inherited from V10

- Delivery VAT treatment not finalised; shipping zones saved in admin are not wired to shipping calculation.
- Stripe, stock reservation expiry, refunds, invoices, backups, mail, permissions and security need end-to-end production rehearsal.
- Imported product stock was zero at last review; verify all supplier SKUs, prices, photographs and actual inventory.
- Pallet specific and Mix & Match volume discount rules remain unimplemented.
- Coupon pending reservations need an operational reconciliation job or process to check Stripe session status and release stale reservations; do not blindly release reservations without checking payment status.

**Status:** Source package, not production-certified. Local PHP/JS syntax and ZIP integrity checks only; Composer/Laravel integration tests must run on the staging server.
