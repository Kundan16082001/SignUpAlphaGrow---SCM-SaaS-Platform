<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseFallbackServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Only attempt fallback if the default is MySQL
        if (config('database.default') === 'mysql') {
            try {
                // Test the MySQL connection
                DB::connection('mysql')->getPdo();
            } catch (\Exception $e) {
                // MySQL failed! Switch to SQLite
                $this->activateOfflineMode();
            }
        }
    }

    protected function activateOfflineMode()
    {
        $sqlitePath = database_path('offline.sqlite');

        // Auto-create the SQLite database file if it doesn't exist
        if (!file_exists($sqlitePath)) {
            touch($sqlitePath);
            // Ensure proper permissions (optional but recommended for Linux/Mac)
            if (PHP_OS_FAMILY !== 'Windows') {
                chmod($sqlitePath, 0777);
            }
        }

        // Dynamically switch the default connection to SQLite
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => $sqlitePath]);

        Log::warning('⚠️ MySQL connection failed. Automatically switched to Offline SQLite mode.');
    }
}