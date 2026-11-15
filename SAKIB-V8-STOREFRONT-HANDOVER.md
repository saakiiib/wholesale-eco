# Wholesale Eco Products — Laravel V8 handover for Sakib

## Base and scope

V8 is a full source copy based on `wholesale-eco-laravel-v7-products-admin.zip`. It retains the V7 supplier range seeder, products/images, dashboards, reports, customer account and existing ordering/checkout logic. It does **not** include any unshared server-side changes made after V7; compare it against the deployed code and merge before applying it. Install to staging first.

## What's new

1. **Live wholesale tier highlighting** on product pages (`/products/{slug}`) and category ranges (`/shop?category=...`). Highlight now follows the selected *per-SKU* quantity: 1–3, 4–9, 10–19, 20+. It does not pool quantities across SKUs for mix-and-match.
2. **Units per Pack / Pallet** display switch on the same matrix. Admin can define pack size (`units_per_pack`), cases per pallet (`packs_per_pallet`), and enable pallet information per SKU. Unconfigured pallet size displays `Not configured`.
3. **Price per Pack / Unit** switch retained. The product variant admin can enable or disable unit price comparison per SKU.
4. **Optional pallet ordering**, available only when an admin sets `packs_per_pallet` and checks `allow_pallet_orders`. A pallet selection multiplies the entered pallet count by configured packs per pallet, and the server stores **packs**, not pallets, in the cart. Pricing remains the ordinary SKU pack-tier pricing. There is **no distinct pallet price/discount** or Mix & Match functionality.
5. **Real business contact details** rendered from Admin → Business Setup (`business_phone`, `business_email`, `support_email`, `business_address`) in the storefront footer and new `/contact` page. No hard-coded telephone/email overrides.
6. **Contact page** with email enquiry form (`POST /contact`), backend validation, honeypot and rate limiting. Configure `MAIL_*` settings. The success message confirms dispatch through Laravel's configured mail transport, not guaranteed inbox delivery.
7. **Copyright and developer credit** in footer: Wholesale Eco Products / Mento Software.
8. **Homepage hero product photography** uses six image assets carried over from V7 supplier products; the hero shows bowls, burger boxes and deli boxes. `php artisan storage:link` is required, and the images must exist in `storage/app/public/catalogue`.
9. **Supplier range unchanged:** 12 saleable SKUs across four product families as listed in the attached two-page *Kraft bowls and takeaway boxes* range sheet; no new order or transaction created. V7 includes `database/seeders/SupplierKraftRangeSeeder.php` and `docs/SUPPLIER-KRAFT-RANGE-PRODUCTS.csv`.

## Database migration

New migration: `database/migrations/2026_11_14_000005_add_variant_ordering_options.php`

Columns on `product_variants`:
- `allow_pallet_orders` BOOLEAN DEFAULT false
- `show_unit_prices` BOOLEAN DEFAULT true
- `show_pallet_units` BOOLEAN DEFAULT true

Existing `units_per_pack` and `packs_per_pallet` columns remain unchanged.

## Deployment steps — staging first

1. Back up current site and database. Compare deployed code with V8; DO NOT overwrite any newer unshared changes.
2. Copy files, install dependencies as appropriate and configure `.env` (database, mail, Stripe test keys).
3. Run `php artisan migrate` (on staging). If approved for live, `php artisan migrate --force` there after the backup.
4. Run `php artisan storage:link` and verify images load at `/storage/catalogue/kraft-bowls.jpg`.
5. Run `php artisan optimize:clear`, `php artisan view:clear` and `php artisan test`.
6. Configure business contact details under `/admin/settings` and verify `/contact` plus footer across home, category, account and checkout. Confirm email works using the staging mail transport.
7. Under Admin → Products → Variants, set cases per pallet and choose whether unit prices, pallet quantities and pallet orders are enabled. Existing products are **pallet-order-disabled** by default.

## Manual acceptance checklist

- Product page and category page: quantity 1 selects tier 1–3; quantity 4 selects 4–9; 10 selects 10–19; 20 selects 20+; decrementing moves highlight back.
- Tier highlight affects **only that SKU row**, not the whole catalogue.
- Case/Unit display toggle correctly calculates pence per individual unit using case price / units per case.
- Units Pack/Pallet toggle shows real counts from variant configuration and `Not configured` when absent.
- Pallet order is available only for explicitly enabled SKUs; 2 pallets × 10 cases per pallet adds 20 cases to basket, with corresponding pricing. Stock limits and server validation prevent invalid values.
- Correct support telephone/email/address from `/admin/settings` on footer and contact page.
- Email form sends through configured `MAIL_MAILER`; rate limiting and validation work.
- No stock or unit prices are overwritten by migration.
- V7 seeded supplier catalogue remains 12 SKUs; stock remains zero until client confirms physical inventory.
- Customer portal and admin portal V7 functionality still works after merge.

## Remaining work / not claimed as complete

- Pallet-specific negotiated discounts and Mix & Match are **not implemented**.
- A grouped checkout may still require final delivery-VAT and stock-reservation work identified earlier.
- Product pricing grid assumes standard display breakpoints 1/4/10/20; if admin configures arbitrary quantities between them, the table may not visually represent every threshold. Validate tier setup before production.
- This package passed PHP and JavaScript syntax checks but not an actual Laravel database/browser test run in this environment.
