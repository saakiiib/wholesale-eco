# Wholesale Eco Products — Laravel V3: Customer + Admin Portals

## Base and scope
Built upon the November 14 V2 homepage/product update, itself based on Sakib's wholesale-eco-main Laravel application. **This is a code-review and staging build, not a production-certified release.** V2 storefront, product details, basket notifications, registration and checkout files are retained.

### Customer portal: `/account`
- Dashboard: business name, order count, paid value, latest orders, quick links.
- Orders `/account/orders`: filtering, detail views, order summary print (explicitly **not a VAT invoice**), existing quick reorder action.
- Saved lists `/account/saved-lists`: view, create from current basket, add list to basket, delete list.
- Delivery addresses `/account/addresses`: add/remove and default address choice, business-ownership checks.
- Business profile `/account/profile`: editable company/trading name, phone, optional VAT number, member name.

### Admin portal: `/admin`
- Overview: paid revenue, order/customer/product counts, recent orders, low stock.
- Orders `/admin/orders`: filter/search, detail and existing paid-only fulfilment status update.
- Products `/admin/products`: create/edit metadata, visibility, description, category and existing path-based image reference.
- Product variants: create/edit SKU, case quantity, pallet count, case price, VAT rate and active status.
- Inventory `/admin/variants`: audited stock adjustments and existing volume price tiers.
- Categories: add categories and optional parent.
- Customers: search, customer detail, linked users and order history, active/suspended state.
- Reports: monthly paid revenue snapshot, all-time paid orders, top products.

## Staging deployment
1. Back up the current database, public uploads and `.env` BEFORE replacing any code.
2. Compare this ZIP with the currently deployed Git branch/ZIP. Merge any changes Sakib made AFTER the V2 snapshot — **do not overwrite newer work blindly**.
3. Run `composer install --no-dev --optimize-autoloader` on staging; for test environments install dev dependencies, too.
4. Copy or retain server-side `.env` (do not share secret keys); verify DB, APP_KEY, mail, Stripe sandbox and storage.
5. `php artisan migrate --force` (V3 does not introduce a new migration; it uses V1/V2 tables).
6. `php artisan storage:link` where needed; `php artisan optimize:clear`.
7. Use a real admin account and trade user to test routes. Admin role must be assigned securely — **never expose an admin registration endpoint**.
8. Run `php artisan test` (including `PortalV3Test`), `php artisan route:list`, and manual browser tests.
9. Keep Stripe in **test mode** until payment-webhook and stock lifecycles pass end-to-end testing.

## Review order: one module at a time
1. Customer dashboard: `/account` — overview cards and recent orders.
2. Customer orders: `/account/orders` — view and reorder, ensure order from another company returns 403.
3. Saved lists: `/account/saved-lists` — save from basket; reload; delete.
4. Addresses and profile: `/account/addresses`, `/account/profile`.
5. Admin dashboard: `/admin` — KPIs, recent orders, low stock.
6. Product and SKU editing: `/admin/products`, then `/admin/products/{id}/variants`.
7. Inventory and price tiers: `/admin/variants` — positive/negative stock adjustments and tier updates.
8. Customers, order fulfilment, reports: `/admin/customers`, `/admin/orders`, `/admin/reports`.
9. Regress storefront: `/`, `/shop`, `/products/{slug}`, `/basket`, `/checkout`, `/register`.
10. Desktop/mobile visual review and browser console/errors.

## Outstanding before live deployment
- Genuine VAT invoices, returns/credit notes, shipping tax rules, courier integration.
- Payment, reservation and stock restoration edge cases (abandoned checkout, webhook retries, overselling).
- Password reset, verified email, team roles, strong MFA for admin and security assessment.
- Full file-upload/image-management pipeline and image optimisation; current admin image field accepts a path, **not an upload**.
- Account users/multiple staff, editing existing addresses, category editing/reordering, bulk imports and advanced reporting.
- Pallet and Mix & Match pricing engine (only core per-variant tiers work).
- Product image copyrights, legal pages, real business delivery/returns policy and account/marketing consent.
- Full testing and approval of the admin suspension policy, checkout details and reconciliation. 
- Reports include paid order revenue and may not match formal accounting treatment; validate with accountant.

## Security considerations
V3 adds an `active-business` middleware alias. It blocks suspended trade users from accessing `/account` and authenticated checkout; suspended users also cannot log in again. Admin actions require `auth` and `admin`. Order and address lookups validate ownership. Admin stock changes retain the existing transactional audit log.

## Environment limitations
PHP syntax was checked on application files. Composer dependencies are absent in this preparation environment, so `php artisan test`, actual DB migrations, rendering and Stripe flows could NOT be executed here. Sakib must run them on staging. No real credentials or live server content are included.
