<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncOfflineData extends Command
{
    protected $signature = 'sync:offline';
    protected $description = 'Syncs offline SQLite data to the online MySQL database';

    // List of models you want to sync
    protected $models = [
        \App\Models\Customer::class,
        \App\Models\Order::class,
        \App\Models\Product::class,
        // Add other Syncable models here
    ];

    public function handle()
    {
        $this->info('🔄 Checking online status...');

        try {
            // Test MySQL connection
            DB::connection('mysql')->getPdo();
            $this->info('✅ MySQL is online. Starting sync...');
        } catch (\Exception $e) {
            $this->error('❌ MySQL is still offline. Sync aborted.');
            return Command::FAILURE;
        }

        $currentDefault = config('database.default');

        foreach ($this->models as $modelClass) {
            $this->info("Syncing {$modelClass}...");
            
            // Get pending records from SQLite
            $pendingRecords = $modelClass::getPendingSyncRecords();

            if ($pendingRecords->isEmpty()) {
                $this->info("  ↳ No pending records for {$modelClass}.");
                continue;
            }

            foreach ($pendingRecords as $record) {
                try {
                    // Temporarily switch to MySQL to perform the upsert
                    $mysqlData = $record->toArray();
                    unset($mysqlData['is_synced']); // Let MySQL handle its own sync state if needed
                    unset($mysqlData['last_synced_at']);

                    // Upsert: Update if ID exists, otherwise Insert
                    // Note: For robust offline-first, using UUIDs instead of auto-increment IDs is highly recommended 
                    // to prevent primary key collisions.
                    DB::connection('mysql')->table($record->getTable())->updateOrInsert(
                        ['id' => $record->id], // Match by ID (or uuid)
                        $mysqlData // Data to update/insert
                    );

                    // Mark as synced in SQLite
                    $record->update([
                        'is_synced' => true,
                        'last_synced_at' => now(),
                    ]);

                    $this->info("  ↳ Synced Record ID: {$record->id}");
                } catch (\Exception $e) {
                    Log::error("Sync failed for {$modelClass} ID {$record->id}: " . $e->getMessage());
                    $this->error("  ↳ Failed to sync Record ID: {$record->id}");
                }
            }
        }

        $this->info('🎉 Sync process completed successfully!');
        return Command::SUCCESS;
    }
}