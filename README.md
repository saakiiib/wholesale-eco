# Wholesale Eco Products — Laravel 12 source package

This is an **installable Laravel application source skeleton with implemented core workflows**, not a tested or launched production system. No Composer dependencies / `vendor` are bundled. The packaged HTML design references under `public/design-preview` remain static **demonstrations** and do not connect to the backend.

## Technology & compatibility
- Laravel 12.x, PHP 8.2+, MySQL 8.x, Stripe PHP SDK.
- Source includes two migration files, Eloquent models, controllers, Stripe Checkout + signed webhooks, session basket, customer account and basic admin functionality.
- Currency values are stored as **integer pence**, VAT in **basis points**, quantities as **whole packs/cases**.
- Tier pricing currently applies **per SKU**, not grouped Mix & Match or pallet bands. This is deliberate: these require further business rules and test coverage.

## Sakib installation / deployment steps
1. Install **PHP 8.2+**, Composer, required PHP extensions (`pdo_mysql`, `mbstring`, `openssl`, `curl`, `xml`, `fileinfo`, `zip`), MySQL and a web server.
2. Unzip in a non-public folder. Run `composer install` (or `composer update` once in a controlled development environment, then commit the resulting `composer.lock`).
3. `cp .env.example .env` and set actual database and SMTP details. Set `APP_DEBUG=false` in production.
4. `php artisan key:generate`
5. `php artisan migrate --force`
6. On staging only: `php artisan db:seed` to insert **illustrative sample products**.
7. Provision an admin securely with `php artisan tinker` and an individually generated password; avoid generic demo administrator credentials.
8. Configure Stripe **test-mode** keys and `STRIPE_WEBHOOK_SECRET`. Add webhook endpoint `https://yourdomain/stripe/webhook` for events `checkout.session.completed`, `checkout.session.expired`, and `checkout.session.async_payment_failed`.
9. Ensure web server document root is the Laravel **`public/` directory**. Serve via HTTPS. Set writable `storage/` and `bootstrap/cache/`.
10. Run `php artisan test`, smoke-test every workflow and verify webhook signatures, idempotency, refunds, shipping and stock accounting before live mode.
11. Confirm product-level tax rules and shipping VAT with the company's accountant. **Current shipping VAT is not added** and must be corrected as appropriate before launch.
12. Replace all illustrative products/prices, delivery thresholds, terms, privacy text, sender domains and branding.

### Local development
`composer install && cp .env.example .env && php artisan key:generate && php artisan migrate && php artisan db:seed && php artisan serve`

### How to create an admin safely (example code in artisan tinker)
`$u = App\Models\User::where('email', 'actual-admin@your-domain.co.uk')->firstOrFail(); $u->update(['role' => 'admin']);`
Only promote a previously registered, verified, trusted account. This command is **not** a public route.

## Routes
- `/` Laravel home; `/shop` catalogue; `/basket`; `/checkout`
- `/register`, `/login`, `/account` (orders, quick reorder, saved shopping lists)
- `/admin` (order dashboard), `/admin/variants` (stock and quantity price bands)
- `/stripe/webhook` (Stripe signature verified; excluded from CSRF only for this endpoint)
- `/design-preview/index.html` etc — original premium HTML prototype, **not connected to production data**.

## Major unfinished items before deployment
- Email verification / password recovery / business-account employee roles; multi-address CRUD.
- Full back-office products/categories/images CRUD; accurate invoices and credit notes; purchase orders; customer support; refunds API; shipping/carrier APIs.
- Fully implemented product group Mix & Match, unit/pallet prices and checkout conversion, customer-specific catalogue and complex wholesale rules.
- Correct delivery tax computation, shipping postcode pricing and legal terms; product data validation.
- Payment reconciliation, automated expiration/release of abandoned stock reservations, delayed payment states, stock reconciliation and refund restock policy.
- Migrations + tests on Sakib's actual production infrastructure, actual Stripe test transactions, real security and load testing.
- Actual premium HTML/JS page integration into secure Laravel Blade templates (basic styled Laravel views are included separately).

**Do not deploy this zip straight to a live domain or claim that payments/fulfilment are production ready.**
