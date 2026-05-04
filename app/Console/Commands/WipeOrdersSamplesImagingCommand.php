<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WipeOrdersSamplesImagingCommand extends Command
{
    protected $signature = 'testing:wipe-orders-samples-imaging
                            {--force : Skip confirmation prompt}';

    protected $description = 'Delete all orders and related samples, imaging studies, results, invoices, and payments. Leaves patients, catalog, users, and equipment intact.';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This permanently deletes all orders and linked lab/imaging/billing data. Continue?', false)) {
            $this->warn('Aborted.');

            return self::SUCCESS;
        }

        $orderCount = (int) DB::table('orders')->count();
        if ($orderCount === 0) {
            $this->info('No orders in the database. Nothing to do.');

            return self::SUCCESS;
        }

        $orderIds = DB::table('orders')->pluck('id')->all();

        DB::transaction(function () use ($orderIds) {
            $resultIds = DB::table('results')->whereIn('order_id', $orderIds)->pluck('id')->all();
            if ($resultIds !== []) {
                DB::table('deliveries')->whereIn('result_id', $resultIds)->delete();
                DB::table('results')->whereIn('order_id', $orderIds)->delete();
            }

            $invoiceIds = DB::table('invoices')->whereIn('order_id', $orderIds)->pluck('id')->all();
            if ($invoiceIds !== []) {
                DB::table('payments')->whereIn('invoice_id', $invoiceIds)->delete();
                DB::table('invoices')->whereIn('order_id', $orderIds)->delete();
            }

            DB::table('imaging_studies')->whereIn('order_id', $orderIds)->delete();
            DB::table('samples')->whereIn('order_id', $orderIds)->delete();
            DB::table('orders')->whereIn('id', $orderIds)->delete();
        });

        $this->info("Removed {$orderCount} order(s) and all related samples, imaging studies, results, invoices, and payments.");

        return self::SUCCESS;
    }
}
