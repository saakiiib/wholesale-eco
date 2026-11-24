<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminMaintenanceController
{
    /**
     * Catalogue + order data only.
     *
     * Child rows are listed before their parents because TRUNCATE does not fire
     * FK cascades: truncating `orders` alone would leave orphaned payments,
     * refunds, shipments and order items behind, and truncating `products`
     * alone would orphan variants, tiers, images and stock movements.
     *
     * Deliberately NOT touched: payment_events (Stripe webhook log), audit_logs,
     * coupons, price_groups, saved_lists, users, businesses, settings, CMS.
     * The order below also matters for the DELETE fallback used when TRUNCATE
     * is not permitted (TRUNCATE needs the DROP privilege).
     */
    private const TABLES = [
        // orders and everything hanging off them
        'refunds', 'payments', 'shipments', 'order_items', 'orders', 'stock_movements',
        // catalogue and everything hanging off it
        'product_images', 'price_tiers', 'customer_prices', 'saved_list_items',
        'product_variants', 'products', 'categories',
    ];

    public function clean(Request $r)
    {
        abort_unless($r->user()?->role === 'admin', 403, 'Administrator access required.');

        $before = [];
        foreach (self::TABLES as $table) {
            $before[$table] = DB::table($table)->count();
        }

        $after = [];
        $mode = 'truncate';
        $fkOff = true;

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } catch (\Throwable $e) {
            $fkOff = false;
        }

        try {
            foreach (self::TABLES as $table) {
                try {
                    DB::statement("TRUNCATE TABLE `{$table}`");
                } catch (\Throwable $e) {
                    // Shared hosts often deny DROP (which TRUNCATE requires).
                    $mode = 'delete';
                    DB::table($table)->delete();
                    DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = 1");
                }
                $after[$table] = DB::table($table)->count();
            }
        } finally {
            if ($fkOff) {
                try { DB::statement('SET FOREIGN_KEY_CHECKS=1'); } catch (\Throwable $e) {}
            }
        }

        $filesDeleted = false;
        if ($r->boolean('files')) {
            Storage::disk('public')->deleteDirectory('products');
            $filesDeleted = true;
        }

        Log::warning('admin.clean-db executed', [
            'user_id' => $r->user()->id,
            'email' => $r->user()->email,
            'ip' => $r->ip(),
            'mode' => $mode,
            'files_deleted' => $filesDeleted,
            'before' => $before,
        ]);

        return response()->json([
            'ok' => array_sum($after) === 0,
            'mode' => $mode,
            'files_deleted' => $filesDeleted,
            'before' => $before,
            'after' => $after,
        ], 200, [], JSON_PRETTY_PRINT);
    }
}
