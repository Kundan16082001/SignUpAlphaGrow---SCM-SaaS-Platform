<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->foreignId('warehouse_bin_id')->nullable()->after('current_qty')->constrained('warehouse_bins')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropForeign(['warehouse_bin_id']);
            $table->dropColumn('warehouse_bin_id');
        });
    }
};