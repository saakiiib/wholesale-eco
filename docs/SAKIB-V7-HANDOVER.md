# Wholesale Eco Products — V7 admin comparison & supplier catalogue import

## Source files used

- Latest client-uploaded Laravel ZIP: `wholesale-eco-main (2).zip` (used as source of truth).
- Client-supplied PDF: `Wholesale-Eco-Products-kraft-bowls-and-takeaway-boxes-45466562.pdf` (two-page product **range sheet**, not a customer transaction invoice).
- Client-uploaded PDF screenshot of currently deployed admin and supplied admin screenshot image (design comparison).

## Product import

The PDF lists 12 sellable pack configurations, grouped into four range pages. The new **`SupplierKraftRangeSeeder`** creates four nested categories under existing `Food Packaging` (`boxes`), four products, twelve variants and their sample supplier images.

**Important:** All PDF prices **include VAT at 20%**. The app stores `base_pack_price_pence` EXCLUDING VAT. We convert each supplier pack price by dividing by 1.20 and rounding to the nearest penny. Normal per-line VAT rounding can occasionally yield a penny difference when ordering multiple packs, so confirm commercial pricing rules before launch.

**Safety controls:**
- No invoices, orders, customers, payments or transactions are generated. This is **catalogue data**, not a sale.
- No guessed opening inventory. All NEW SKUs start at **0 packs** and cannot be ordered until staff verifies and adds stock through the inventory UI.
- No invented volume discounts or Mix & Match tiers.
- Existing SKU prices, inventory and other merchant-edited records are never overwritten by re-running the seeder.
- New SKU identifiers (`WEP-…`) are generated for system use and must be confirmed before connecting to a warehouse/POS.
- Product images are extracted from the client-provided PDF. Images are group-level illustrations, not guaranteed exact photos of every capacity/lid variant.
- Bowl/lid fit relationships are preserved as variant attributes; compatibility automation is not yet implemented.

The exact SKU/price list is `docs/SUPPLIER-KRAFT-RANGE-PRODUCTS.csv`.

## Admin portal comparison

The uploaded **deployed admin** PDF shows a dark green sidebar, business overview hero, four data-driven KPIs, sales bars, order pipeline, recent orders and inventory alerts. This **already matches the style of the earlier approved Wholesale Eco admin prototype**, so the existing V6 portal shell is retained.

The separate screenshot reference includes extra widgets that are not yet present in that deployed admin dashboard:

| Reference widget | V7 implementation | Notes |
| --- | --- | --- |
| Top-selling products | **Added**, from paid order item totals | No unrelated sample products |
| Shipment status | **Added**, from paid order fulfilment states | Not a real-time courier feed |
| Customer activity | **Added**, active accounts, new accounts in 30 days, purchasing businesses in 30 days | Database-derived |
| Marketing performance | **Added as honest integration placeholder** | Requires GA4 / attribution integration; no fabricated clicks or conversion figures |
| Sales overview | **Retained** | Real seven-day paid sales |
| Inventory status / low stock | **Retained** | Existing live stock KPIs and alerts |
| Recent orders | **Retained** | Live order table |
| Store modules and navigation | **Retained** | Existing V6 business, stock, shipping, CMS, SEO, team and audit screens |

The user-provided screenshot has a generic product list such as headphones and smart watches; those are **not** added to this food-packaging wholesale store.

## Sakib / Shakil — staging deployment steps

1. Back up existing application code, uploaded files, `.env`, and MySQL database. Compare this ZIP against latest server changes and merge rather than blindly replacing files.
2. Install code and dependencies using current environment, keeping live `.env` secrets on the server.
3. Run existing migrations first: `php artisan migrate --force` (the V7 catalogue seeder requires V6 migration tables).
4. Preserve the imported image files in `storage/app/public/catalogue/`. Confirm `php artisan storage:link` (once) and filesystem permissions.
5. **Preview the CSV** and confirm product SKUs and VAT assumptions with client.
6. Import only the supplier catalogue via `php artisan db:seed --class=Database\Seeders\SupplierKraftRangeSeeder --force`. **Do NOT run the generic demo DatabaseSeeder in production.**
7. Run `php artisan optimize:clear` and `php artisan test --filter=SupplierKraftRangeTest` plus full test suite.
8. Check `/admin`, `/admin/products`, `/admin/categories`, `/admin/variants`; confirm all 12 SKUs in their appropriate product groups and accurate ex-VAT pack prices.
9. Confirm representative pages: `/shop?category=boxes`, `/shop?category=kraft-bowls-and-lids`, `/products/kraft-bowls-and-pp-lids`, `/products/kraft-clamshell-burger-boxes`, `/products/kraft-deli-takeaway-boxes`, `/products/rectangular-kraft-takeaway-boxes-and-lids`.
10. After physical inventory check, enter **real pack quantities** in admin; until then SKUs will show as unavailable to order.
11. Check dashboard on desktop and mobile, and that new widgets remain accurate with zero orders.

## Outstanding production work

- Verify commercial product SKU naming, inventory, VAT classification and packaging documentation with client.
- Delivery-zone rules not fully connected to checkout; configure and test before live payment use.
- No automated PP-lid compatibility suggestions yet; variants describe exact fits.
- GA4 / paid campaign metrics are not wired into admin reports.
- Full warehouse fulfilment/courier integration and sophisticated pallet/Mix & Match pricing are separate workstreams.
- Live Stripe, invoice, security and order flow need full staging testing before production release.

## Verification in this environment

PHP syntax checking and ZIP file validation can be run here. Laravel's actual feature tests require Composer dependencies, application configuration and a working test database, and must be run by the deployment engineer.
