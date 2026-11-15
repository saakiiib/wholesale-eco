# Wholesale Eco Products — V12 storefront and launch audit

Base: `wholesale-eco-laravel-v11-sorting-vat-coupons.zip` available in the conversation, **not a dump of Sakib's current production repository**. Merge any newer production changes; install on a staging clone, never overwrite production blindly.

## Delivered in V12

1. Both `resources/views/shop/category.blade.php` and `resources/views/shop/product.blade.php`: removed the three separate Capacity, L × W × H, Type columns and placed available specifications under the product name in short coloured labels. Preserved pack counts, each of four wholesale price tiers, stock status, and the basket action.
2. `public/css/v12-commerce.css`: balanced desktop matrix so the order cell remains in view at typical desktop widths; mobile cards display the quantity and Add button without sideways scrolling. Existing quantity-based tier highlighting is preserved.
3. Category overview: consolidated client-side filtering and full-category form search into one input; moved the single search panel below the gallery/description so it fills the width. Live filtering works on the loaded page; form submission searches all paginated products.
4. Admin product image gallery: upload up to 8 images in a single request; reorder existing photos with up/down actions, validated and logged on the server. Routes `admin.images.store`, `admin.images.move`, `admin.images.destroy`. Existing product image models and the storefront gallery continue to be used. Photo ordering does not automatically reassign `products.image_path` (main/cover photo).
5. SEO baseline: canonical link for publicly indexable store routes, generated `sitemap.xml` of published products/categories, `robots.txt` and noindex directives on basket/checkout/account/auth/admin pages. More SEO work required below.
6. Rate limits on login (10 requests/min) and registration (5 requests/min). These do not replace CAPTCHA, monitoring, email verification, MFA, or penetration testing.

No schema migration is required for V12.

## Stage tests for Sakib

- Merge changes against latest deployed code. Back up database/files first.
- `composer install`, `php artisan optimize:clear`, `php artisan route:list`, `php artisan test` on staging.
- Review `shop?category=kraft-bowls-and-lids`, `shop?category=kraft-deli-boxes`, and `products/kraft-bowls-and-pp-lids` at 1440 px, 1024 px, 768 px and 375 px widths. Verify **Add to Basket is always visible on mobile**.
- Test tier highlight at 1, 4, 10, 20 packs; test stock=0 and stock >0; test price/pack-vs-unit and pallet conversions. Server price must be authoritative.
- Upload 3 images to a product via `/admin/products/{id}/images`; check image size/type restrictions, order up/down, gallery thumbnail selection, image removal and cleanup. Verify another product's images cannot be managed through a mismatched URL.
- Verify product card and full-gallery images load from production storage: `php artisan storage:link`, appropriate permissions, configured host/URL. Check old catalogue images as well.
- Verify search both as-you-type within current page and submit across a category, including pagination; reset should clear both current filter and active server search.
- Check generated `/sitemap.xml`, `/robots.txt`, canonical URLs, noindex controls, meta titles/descriptions and Google Search Console crawlability.

## Launch approval: NO — outstanding blockers and checks

### Critical (must resolve and verify)

- Stripe test payments, delayed/duplicate webhooks, cancellations, coupon reservation/release, refunds, and payment reconciliation across network interruptions.
- Stock allocation under concurrency, order expiry, stock returns and overselling protection. Supplier imports have **zero stock** until a real count is entered.
- Delivery tax remains unfinished in `PricingService`; admin shipping zones are not demonstrably applied at checkout. Confirm VAT rules with the merchant/accountant.
- Invoice template is explicitly a **draft**; VAT-inclusive delivery tax, unique issued invoice numbering, legally required supplier details, credit notes/refunds and historical seller snapshots require completion.
- The **public site observed 2026-11-15** still displayed `020 7946 0000`, `support@example.test` and an example street address. Correct the *live database* in Admin → Business Setup and verify public pages before accepting orders.
- Confirm company ownership, trading information, returns/delivery terms, privacy/cookie policy, secure production Stripe credentials and real tested support email.

### Security / infrastructure (no production sign-off without validation)

- Confirm production `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://...`, HTTP-only/Secure/SameSite cookies, HTTPS redirect/HSTS and restricted file permissions. `.env.example` is for local development and contains illustrative demo credentials; do not use them live.
- Verify staff permission isolation, business-level data access, CSRF, upload restrictions, abuse throttling, password-reset, email verification, admin MFA, backup restoration, server/DB patching, log retention, alerting and incident response.
- Carry out a third-party vulnerability scan and real-browser test; static code review does **not** prove security.
- Cookie consent script stores choices, but blocking optional trackers until consent must be verified with the actual deployed analytics/advertising scripts.

### SEO and merchandising checks

- Sitemap/robots/canonical baseline added. Still verify canonical design for paginated and filtered URLs, Open Graph/social previews, product structured data, unique metadata and image alt text. SEO should only include actually published and purchasable catalogue pages where applicable.
- Current product categories contain empty/stock-zero ranges and sometimes same pricing in all tiers; confirm stock, product descriptions, unit conversion, discounts and contact details.
- Test mobile performance, page speed, loading images, broken links, accessibility, cookie banner and content clarity.

## Verification completed here

- PHP syntax checks and static source inspections.
- ZIP structure/integrity checks.
- Browser tests, Composer test suite, database migrations, real Stripe and actual production configuration **not run in this environment**.
