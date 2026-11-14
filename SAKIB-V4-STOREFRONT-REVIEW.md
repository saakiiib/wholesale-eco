# V4 Storefront Review — Wholesale Eco Products

## Scope

This package is based on the complete V3 Laravel archive, **not** an isolated set of HTML pages. V3's customer portal, admin portal, checkout and existing routes are retained.

### 1. Full-width homepage hero

- Updated `resources/views/shop/home.blade.php` with an edge-to-edge `eco-hero-bleed` wrapper.
- Hero text/illustration remain aligned within a 1280px central content container while the pale-green background spans the viewport.
- Updated `public/css/eco-upgrade.css` with responsive full-bleed styles.
- Existing homepage sections and navigation are unchanged.

### 2. Reference-inspired product/category detail page

- Replaced `resources/views/shop/product.blade.php` with a fuller product landing page based on the **layout concepts** shown in the supplied Kite screenshot (not a copy of the brand or proprietary assets):
  - category navigation sidebar;
  - gallery at top-left with support for uploaded image thumbnails;
  - description, factual product information and a quick variant search panel at top-right;
  - product/pricing tabs;
  - responsive bulk SKU table showing units per case, 1–3 / 4–9 / 10–19 / 20+ per-SKU price tiers, stock and case quantity entry;
  - case/unit **price display toggle**; unit price shown in pence;
  - client-side filtering by label, SKU and any defined variant attribute values;
  - +/- buttons and working HTML form posting to the existing `cart.add` route;
  - supplementary details and trade account links.
- Updated `ShopController::product` to provide current active categories as sidebar navigation.
- Added `public/css/kite-product-v4.css` and `public/js/kite-product-v4.js`.

## Important differences from the reference

- Pallet pricing, 'Mix & Match' discount calculation, and advanced material/colour attribute chips are **not implemented**, because the current V3 backend does not support the full rules. UI explicitly marks the first two as future features. Do not publish these options as working until the database schema, pricing service, customer messaging and tests support them.
- This implementation is original and matches the *layout/functionality*, not an exact reproduction of Kite's branded design.
- Image thumbnails show uploaded images only. Supply product photography and ensure Laravel's storage symlink exists.
- Current pricing tiers are per product variant; the server remains authoritative on totals and stock.

## Files changed

- `resources/views/shop/home.blade.php`
- `resources/views/shop/product.blade.php`
- `app/Http/Controllers/ShopController.php`
- `public/css/eco-upgrade.css`

## Files added

- `public/css/kite-product-v4.css`
- `public/js/kite-product-v4.js`
- `SAKIB-V4-STOREFRONT-REVIEW.md`

## Deploy to staging (never overwrite the live database without backup)

1. Back up the deployed V3 files, `.env`, uploaded product images and database.
2. Compare the package against any post-V3 work before copying changes. Merge rather than overwrite if Sakib has newer modifications.
3. Deploy V4 to staging. Do not replace your environment-specific `.env`.
4. `composer install --no-dev --optimize-autoloader` on production-like environments, or `composer install` for test environments.
5. `php artisan optimize:clear`
6. `php artisan storage:link` if not already in place.
7. Run `php artisan test` on staging with configured test database.
8. Check `/`, one product `/products/{slug}`, `/shop`, `/basket`, `/account` and `/admin`.
9. Test desktop at 1440px and mobile at 390px, including price toggle, filters, quantity +/- and item addition.
10. Verify SKU-specific discounts and VAT are still calculated accurately in the real basket/checkout.

No new migrations are required for these layout and controller changes.

## Test limitations

The build environment ran PHP syntax checks and ZIP checks. Composer vendor dependencies and MySQL are not configured here, so Laravel rendering, JavaScript browser execution and end-to-end tests must be verified on staging.
