<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('warehouse_capacity_config', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->string('warehouse_name')->default('Main Warehouse');
        $table->decimal('total_area_sqft', 10, 2)->default(0);
        $table->integer('pallet_capacity')->default(0);
        $table->integer('shelf_capacity')->default(0);
        $table->integer('cold_capacity')->default(0);
        $table->integer('hazmat_capacity')->default(0);
        $table->decimal('max_weight_tons', 8, 2)->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_capacity_config');
    }
};
