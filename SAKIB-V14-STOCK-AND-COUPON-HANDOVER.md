# Wholesale Eco Products — V14 Stock Consistency and Basket Layout

Based on the latest user-uploaded `wholesale-eco-main (5).zip` (16 November 2026).

## What changed
- `CartController::add`: rejects inactive parent products and inactive SKUs, zero-stock items, requests exceeding available stock, and cumulative cart quantities that exceed stock. A previous bug checked the newly added quantity but not the sum already in the basket.
- `CartController::update`: validates the updated cart quantity against stock and product/variant activity. A quantity of zero still removes an item.
- `/shop` catalogue: clearly displays **Out of stock** and disables both quantity input and Add button for unavailable variants. Limits the quantity field to recorded stock when available.
- Individual product and category price matrices: show **Unavailable** instead of an apparently actionable Add button for zero-stock SKUs. Their existing price-preview behaviour remains.
- Basket coupon: new responsive input/button arrangement using dedicated CSS; no overlapping Apply and Remove buttons.
- Added feature tests for zero-stock, cumulative cart quantity, updates above stock, and inactive products.

## Deployment
1. Back up code and database and deploy on **staging**, merging any newer server changes.
2. Verify the latest real stock counts in Admin > Inventory. Stock quantity zero intentionally prohibits ordering.
3. Install dependencies, run `php artisan test --filter=V14StockConsistencyTest`, `php artisan test`, then `php artisan optimize:clear`.
4. Visit `/shop`, `/shop?category=boxes`, `/products/box-750ml`, and `/basket` on desktop and mobile.
5. Test: SKU with 0 stock; SKU with 2 cases (add 2, then try to add 1); update existing basket to 3; inactive SKU and inactive parent product; pallet ordering; coupon Apply and Remove.
6. Complete payment and concurrent stock reservation tests before enabling production sales. Laravel transaction locking during order creation remains in the existing OrderService.

## Important caveat
This package corrects ordering checks and UI state, but **does not certify production readiness**. The pre-existing delivery VAT logic is incomplete; tax invoices and Stripe/webhook and stock-reservation recovery still require controlled end-to-end testing. Exact live database stock counts cannot be inferred from the source ZIP.

## Expected behaviour
All active products may remain **visible** in the catalogue regardless of stock, for browsing and SEO. Only active variants with sufficient current stock may be **purchased**. The backend must remain authoritative even if a browser modifies the HTML.
