<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'is_synced')) {
                $table->boolean('is_synced')->default(true)->after('status');
            }
            if (!Schema::hasColumn('customers', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable()->after('is_synced');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'is_synced')) {
                $table->dropColumn('is_synced');
            }
            if (Schema::hasColumn('customers', 'last_synced_at')) {
                $table->dropColumn('last_synced_at');
            }
        });
    }
};