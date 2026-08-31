<?php

namespace App\Console\Commands;

use App\Models\InventoryStock;
use App\Models\Outlet;
use App\Models\StockBatch;
use App\Models\StockLedger;
use App\Models\WarehouseBin;
use App\Models\WarehouseZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedEnterpriseWmsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wms:seed-enterprise';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed Authentic Enterprise WMS Master Zones, Bins, and Put-Away unassigned inventory stocks.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Enterprise WMS Master Data Seeding & Stock Put-Away...');

        DB::transaction(function () {
            $outlets = Outlet::all();

            if ($outlets->isEmpty()) {
                $this->error('No Outlets found in database!');
                return;
            }

            foreach ($outlets as $outlet) {
                // 1. Create Inbound Receiving Zone
                $inboundZone = WarehouseZone::firstOrCreate([
                    'outlet_id' => $outlet->id,
                    'name'      => 'Inbound Receiving Zone',
                ], [
                    'type'   => 'active',
                    'status' => 1,
                ]);

                // 2. Create Main Storage Zone
                $storageZone = WarehouseZone::firstOrCreate([
                    'outlet_id' => $outlet->id,
                    'name'      => 'Main Storage Zone',
                ], [
                    'type'   => 'active',
                    'status' => 1,
                ]);

                // 3. Create Outbound Dispatch Zone
                $dispatchZone = WarehouseZone::firstOrCreate([
                    'outlet_id' => $outlet->id,
                    'name'      => 'Outbound Dispatch Zone',
                ], [
                    'type'   => 'active',
                    'status' => 1,
                ]);

                // Bins for Inbound Zone
                $dockBin = WarehouseBin::firstOrCreate([
                    'zone_id' => $inboundZone->id,
                    'barcode' => 'BIN-' . $outlet->id . '-DOCK-01',
                ], [
                    'name'   => 'Receiving Dock Bay 1',
                    'status' => 1,
                ]);

                // Bins for Storage Zone
                $rackA1Bin = WarehouseBin::firstOrCreate([
                    'zone_id' => $storageZone->id,
                    'barcode' => 'BIN-' . $outlet->id . '-A1-S1',
                ], [
                    'name'   => 'Rack A1 - Shelf A',
                    'status' => 1,
                ]);

                $rackB1Bin = WarehouseBin::firstOrCreate([
                    'zone_id' => $storageZone->id,
                    'barcode' => 'BIN-' . $outlet->id . '-B1-S1',
                ], [
                    'name'   => 'Rack B1 - Shelf B',
                    'status' => 1,
                ]);

                // Bins for Dispatch Zone
                $dispatchBin = WarehouseBin::firstOrCreate([
                    'zone_id' => $dispatchZone->id,
                    'barcode' => 'BIN-' . $outlet->id . '-DISP-01',
                ], [
                    'name'   => 'Dispatch Bay 1',
                    'status' => 1,
                ]);
            }

            $this->info('Master Zones & Bins created for all outlets.');

            // 4. Put-Away Unassigned Inventory Stocks
            $unassignedStocks = InventoryStock::whereNull('bin_id')->get();
            $this->info("Found {$unassignedStocks->count()} unassigned stock records. Assigning to Enterprise Storage Bins...");

            $assignedCount = 0;

            foreach ($unassignedStocks as $stock) {
                // Find primary storage bin for this outlet
                $targetBin = WarehouseBin::whereHas('zone', function ($q) use ($stock) {
                    $q->where('outlet_id', $stock->outlet_id)->where('type', 'active');
                })->first();

                if (!$targetBin) {
                    // Fallback to any bin in outlet or global bin 1
                    $targetBin = WarehouseBin::whereHas('zone', function ($q) use ($stock) {
                        $q->where('outlet_id', $stock->outlet_id);
                    })->first() ?? WarehouseBin::first();
                }

                if ($targetBin) {
                    $stock->bin_id = $targetBin->id;
                    $stock->save();

                    // Sync StockBatches
                    StockBatch::where('outlet_id', $stock->outlet_id)
                        ->where('product_id', $stock->product_id)
                        ->where('variant_id', $stock->variant_id)
                        ->whereNull('bin_id')
                        ->update(['bin_id' => $targetBin->id]);

                    // Sync StockLedgers
                    StockLedger::where('outlet_id', $stock->outlet_id)
                        ->where('product_id', $stock->product_id)
                        ->where('variant_id', $stock->variant_id)
                        ->whereNull('bin_id')
                        ->update(['bin_id' => $targetBin->id]);

                    $assignedCount++;
                }
            }

            $this->info("Successfully put-away {$assignedCount} inventory stock items into Enterprise Bins!");
        });

        $this->info('Enterprise WMS Seeding Complete!');
    }
}
