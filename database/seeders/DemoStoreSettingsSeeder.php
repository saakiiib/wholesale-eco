<?php
namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

/**
 * DEMO ONLY — sensible placeholder business details for a fresh local install.
 *
 * The storefront footer, contact page and invoice templates read these values
 * from Admin → Business Setup. Without them the contact form refuses to send
 * and the footer has no phone/email. Existing merchant-edited values are never
 * overwritten.
 */
class DemoStoreSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'business_name' => 'Wholesale Eco Products',
            'business_email' => 'support@example.test',
            'support_email' => 'support@example.test',
            'business_phone' => '020 7946 0000',
            'business_address' => "Wholesale Eco Products\n1 Example Street\nLincoln\nLN1 1AA",
            'low_stock_threshold' => '10',
        ];

        foreach ($defaults as $key => $value) {
            StoreSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
