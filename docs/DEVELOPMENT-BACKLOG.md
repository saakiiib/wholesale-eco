# Development backlog for Sakib (prioritised)

## P0 — production blockers
- ~~Install and verify Laravel 13 / PHP/MySQL dependency compatibility and regenerate Composer lockfile.~~ **Done (local dev only): downgraded to Laravel 12.x on PHP 8.2; Composer lockfile generated; MySQL 10.4 (MariaDB) running; schema migrated and seeded.**
- Implement shipping VAT and actual delivery tariff rules; verify product tax classes with accountant.
- Replace every demo value with real customer-approved master data.
- Implement queue-backed payment email notices, invoice document generation and audit trail for refunds.
- Design Stripe Checkout reservation expiry, async payments and support recovery; scheduled reconciliation for orphan sessions.
- End-to-end test Stripe signed events, retries, duplicate events, order locks, stock releasing and cancellations.
- Enforce email verification, customer account ownership controls, admin 2FA and rate-limits.
- Integrate the polished five-page HTML prototype with real Blade/Livewire data and remove all mock localStorage logic.
- Security audit, test suite, staging deployment, backups/restore rehearsal and live cutover checklist.

## P1 — feature completeness
- Advanced category/product/variant CRUD, bulk CSV import/export and media processing.
- Business and user profile editing, multiple shipping addresses, delegated staff accounts.
- Product group-based Mix & Match and pack/unit/pallet toggles, exact quantity conversion.
- Customer-specific contract prices and promotion precedence.
- Shipment management, tracking, returns, stock correction and refund fulfilment workflow.
- Saved lists editing, reorder variation substitutions, customer support tickets and search.
- Real VAT invoices, credit notes and accountant export.

## P2 — subsequent enhancements
- Xero integration, multi-location ordering, repeat schedules, supplier purchasing, back-in-stock notifications and analytics dashboards.
