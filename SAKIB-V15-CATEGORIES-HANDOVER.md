# Laravel V15 — Category hierarchy and admin usability

Base: V14 stock/coupon package. Merge with any newer server changes before deployment.

## Changes
1. Shared nested category navigation in product/category catalogue views. Parents remain clickable; arrow expands children. Active category ancestry is expanded on page load.
2. Sidebar has an independent scroll area on desktop and capped scroll area on mobile.
3. Admin /admin/categories now has a top **Add category** button opening a native accessible dialog; the existing POST store route and validations remain unchanged.
4. Existing admin category list, edit and sorting functions remain unchanged. Auto-generated slug is an editable convenience, not a server guarantee.

## Testing
- Back up source and database, deploy to staging, `php artisan view:clear`, `php artisan optimize:clear`.
- Check parent category `kraft-bowls-and-takeaway-boxes` expands to the four children, in saved position order.
- Check parent links show descendant products; links for child categories filter only that child.
- Check /shop, /shop?category=kraft-bowls, and a child product's detail page.
- Scroll inside the sidebar while the main body remains stationary on a desktop viewport.
- Check keyboard tab navigation, toggle buttons and mobile navigation.
- Admin > Categories > Add category: create, validation, duplicate slug, parent and saved sorting order.
- If a category is listed with zero products, assign the correct products using Admin > Products; changing the category structure alone does not reassign products.
- Verify existing coupon, checkout, customer/admin portals unchanged.

## Limitations
- No application/database execution in preparation environment; PHP syntax and ZIP integrity alone are not end-to-end approval.
- This patch is based on V14, not an undisclosed later deployment.
