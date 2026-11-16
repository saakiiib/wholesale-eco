# Wholesale Eco Products — Laravel V16 order dispatch release

**Base:** Laravel V15 ZIP from the project conversation. Keep all newer changes Sakib has made after V15 by comparing and merging; do not overwrite production without backups.

## What changed

1. **Single order status form** (`/admin/orders/{order}`): when `Dispatched` is selected, optional courier and tracking fields appear **before** the Save Order Status button. For `Processing`, fields are hidden.
2. **One save action:** it saves the status and optional shipment tracking, then sends an HTML customer dispatch confirmation, with or without tracking.
3. **Dispatch email:** `app/Mail/OrderDispatched.php` plus `resources/views/mail/order-dispatched.blade.php`, styled to match existing order confirmation mail. Reads store identity/contact from `store_settings`.
4. **No routine duplicate mail:** a dispatch email is sent on the first transition into `dispatched`. A successful send records `dispatch_email_sent_at`; repeated saves do not send it again. A failed send leaves `dispatch_email_status=failed` and displays an explicit **Retry dispatch email** button on the order page.
5. **Order list and detail:** show recorded `payments.provider` (Stripe, Paypal, etc.) next to the payment status. Don't guess a provider when there is no payment record.
6. **Invoice footer:** prints the configured business name, address, phone, email, optional support email, VAT and company number.
7. The old `/admin/orders/{order}/tracking` action is retained for backward compatibility but now goes through the same logic; the duplicate standalone tracking form has been removed.

## Database

New migration: `database/migrations/2026_11_16_000001_add_dispatch_notification_to_orders.php` adds nullable `dispatch_email_sent_at`, `dispatch_email_status`, `dispatch_email_claimed_at`. No destructive changes to current orders.

## Installation on staging

1. Back up **database, storage uploads and project files**.
2. Compare this ZIP with the latest deployed project and merge changes.
3. Set working outbound email settings using a verified sender address and the correct SMTP credentials in `.env` (do not upload secrets to chat).
4. Run `composer install`, `php artisan migrate`, `php artisan optimize:clear`, `php artisan test --filter=V16DispatchWorkflowTest` and the full suite `php artisan test`.
5. Test on desktop/mobile: choose Dispatched; tracking panel appears; submit empty fields; verify status, customer email, no bogus tracking section, timestamp, and no repeat on second save.
6. Repeat with courier and tracking provided; check the saved shipment record and email. Test a deliberately failed mail sender, then the admin retry button.
7. Verify an unpaid order cannot dispatch, customer accounts cannot use admin action, delivered orders cannot be moved backwards, and production provider labels match real `payments.provider` values.
8. Check printed invoice footer and responsive email in Gmail and Outlook.

## Important operational considerations

- **Do not deploy directly to production without staging test results.** The V16 code has been syntax-checked, not run against a database or a real mail provider in this container.
- A delivery email may be resent if an email provider accepts a message but the application crashes before recording success. This is a standard at-least-once delivery edge case; confirm in the mail provider logs before manually retrying a stale `sending` state.
- An existing order already marked `dispatched` before V16 will not be emailed simply because of deploying the migration. If needed, use the explicit retry action after checking whether the customer already received a dispatch email.
- Existing invoice is still flagged **DRAFT** until VAT, invoice numbering and delivery tax handling are certified. V16 adds contact details only; it does not issue certified VAT invoices.
- V16 does not add PayPal checkout. It only displays the provider already stored on the payment record; the available payment gateway implementation remains as deployed.
- Preserve the wider stock, payment, delivery tax, data protection and security audit requirements from previous releases.

## Modified files

- `app/Http/Controllers/AdminOrderFulfilmentController.php` (new)
- `app/Mail/OrderDispatched.php` (new)
- `resources/views/mail/order-dispatched.blade.php` (new)
- `database/migrations/2026_11_16_000001_add_dispatch_notification_to_orders.php` (new)
- `app/Models/Order.php`
- `app/Http/Controllers/AdminPortalController.php`
- `resources/views/portal/admin/order.blade.php`
- `resources/views/portal/admin/orders.blade.php`
- `resources/views/portal/invoice.blade.php`
- `routes/web.php`
- `tests/Feature/V16DispatchWorkflowTest.php` (new)
