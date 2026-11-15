# V7 changelog

- Imported 12 source-listed pack configurations from the 2-page client supplier catalogue using a dedicated idempotent Laravel seeder.
- Added four nested Food Packaging categories and four range-level products.
- Included genuine group photography extracted from the supplied PDF in `storage/app/public/catalogue/`.
- Used documented VAT-inclusive-to-exclusive integer pence conversion, zero unverified stock, no invented tier pricing.
- Enhanced the existing premium V6 admin dashboard with real top-product stats, shipment states, customer activity and an explicit unconfigured marketing placeholder.
- Added `tests/Feature/SupplierKraftRangeTest.php`, `docs/SUPPLIER-KRAFT-RANGE-PRODUCTS.csv` and `docs/SAKIB-V7-HANDOVER.md`.

**Deploy on staging, review the CSV and run only the dedicated supplier-range seeder.**
