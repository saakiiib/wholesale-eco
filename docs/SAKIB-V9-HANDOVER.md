# Wholesale Eco Products — Laravel V9 handover

## Scope and safe deployment
This package was derived from the supplied V8 source snapshot, not any further changes made on the live server after V8. Do **not** overwrite newer deployed code without merging and testing. Deploy to staging only; back up code, database and files before changes.

## Changes
1. **Hero photo reliability:** Photographs from the supplied supplier range sheet are copied into `public/images/hero`, avoiding a dependency on a missing `storage` symlink for the hero. Real final client-approved imagery is still recommended.
2. **Admin role editor:** New `admin_roles` table and `users.admin_role_id`. Superadmin (existing `role=admin`) can create/edit permission groups, create restricted staff, assign roles. `EnsureAdmin` enforces a conservative route-name permissions allowlist; new unlisted routes deny staff by default. Superadmin-only mutations prevent staff granting themselves permissions.
3. **Category editing:** Already present: Admin → Product Catalogue → Categories, create with optional parent, edit with cycle checks. New supplier import is separate, intentionally not run automatically.
4. **Supplier products:** `database/seeders/SupplierKraftRangeSeeder.php` contains 12 SKUs from the provided *Kraft bowls and takeaway boxes* range sheet. Prices from the sheet include 20% VAT; stored base pack prices are ex VAT. Import is idempotent and does not overwrite merchant edits. Run only after reviewing supplier categories, pictures, and sample/real catalogue. All 12 SKUs have starting stock **zero**; obtain actual stock before enabling checkout. Source is a range sheet, not a paid customer order.
5. **Price matrix:** Added columns for capacity, L×W×H and type; dropdown pallet quantities and per-unit display remain from V8. Tier highlighting recalculates for entered quantity. Special pallet discounts and mix-and-match pricing **still not implemented**, never advertise these as enabled.
6. **Cookies:** `/cookies`, opt-in/out banner and browser-stored choices. No optional tracking script should load until explicit consent, and the site owner must integrate consent into any third-party scripts. Audit actual cookies and retention before launch.
7. **Footer:** Facebook/Instagram/LinkedIn HTTPS links and WhatsApp number editable under Admin → Business Setup; footer links only appear when configured. WhatsApp link uses `wa.me` with digits.
8. **Orders:** Status-filter tabs for All, Pending, Processing, Dispatched, Collected, Returned and Cancelled. Collected/Returned are read-only filters and must be implemented in business workflows, with authorization and stock/refund rules, before their status buttons can be enabled.
9. **Customer list:** Created date, primary user email, phone, active state, user/order counts.
10. **Invoice / statement:** Branded print-friendly order document for customer and admin; uses order snapshots and current business settings. Shows VAT invoice heading only for **paid** orders when a VAT number has been configured. This is an interim **DRAFT** HTML/print-PDF presentation, NOT an issued VAT invoice, certified accounting document or immutable numbering. Seller/address/VAT must be snapshot at invoice issue; validate all VAT/delivery math and unique invoice numbering before production.

## Deploy
```
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
# staging only:
php artisan test
```
Do not run demonstration seeding on production. `DatabaseSeeder` is now disabled by default unless `ENABLE_DEMO_SEEDER=true` is explicitly set in a disposable staging environment. On staging, to import exactly the 12 supplier SKUs, **only after verification**:
```
php artisan db:seed --class=Database\Seeders\SupplierKraftRangeSeeder
```
Note: Laravel may also accept `--class=SupplierKraftRangeSeeder` depending on version and namespace resolution.

## Manual review sequence
A. Confirm 12 products, ex-VAT price conversion, matching lids and 0 stock. B. Check admin role creation, staff permissions, forbidden access and full admin recovery. C. Check `/shop`, `/products/...` with 1,4,10,20 pack quantities, toggling units and pallet conversion; verify `CartController` totals server-side. D. Check footer and `/cookies`, test consent with browser storage cleared. E. Check customer created dates and order status filters. F. Verify branded order document printed as PDF across 1- and multi-page orders; verify VAT invoice compliance before enabling real invoices.

## Remaining for client handover
- Automated VAT invoice issuance with immutable invoice number, original seller/customer snapshots, tax rates and delivery VAT; refund/credit notes.
- Collection/return/refund workflows; secure payment webhook and overselling concurrency tests.
- Pallet-specific discounts and Mix & Match across SKUs.
- Accessibility/mobile QA, proper cookie scanner and consent-integrated analytics.
- Supplier confirmation of inventory and product suitability/compliance.
- Production mail/Stripe/courier/backup configuration and security review.
