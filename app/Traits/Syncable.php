<?php

namespace App\Traits;

trait Syncable
{
    protected static function bootSyncable()
    {
        // When a record is created or updated, mark it as not synced
        static::saving(function ($model) {
            // Only mark as unsynced if we are currently in offline (SQLite) mode
            if (config('database.default') === 'sqlite') {
                $model->is_synced = false;
            }
        });
    }

    /**
     * Get records that need to be synced to MySQL
     */
    public static function getPendingSyncRecords()
    {
        return static::where('is_synced', false)->get();
    }
}