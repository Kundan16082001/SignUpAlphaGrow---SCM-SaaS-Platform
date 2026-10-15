<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('batches', function (Blueprint $table) {
            // Only add if not already exists
            if (!Schema::hasColumn('batches', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->after('tenant_id')->constrained('warehouses')->nullOnDelete();
            }
        });
    }

    public function down()
    {
        Schema::table('batches', function (Blueprint $table) {
            if (Schema::hasColumn('batches', 'warehouse_id')) {
                $table->dropForeign(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            }
        });
    }
};