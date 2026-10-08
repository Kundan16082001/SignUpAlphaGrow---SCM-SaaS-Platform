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
    Schema::create('warehouse_bins', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->string('bin_code')->unique();
        $table->string('type')->default('shelf'); // pallet, shelf, cold, hazmat
        $table->string('zone')->nullable();
        $table->string('aisle')->nullable();
        $table->string('rack')->nullable();
        $table->integer('max_capacity')->default(100);
        $table->integer('current_qty')->default(0);
        $table->string('temperature_zone')->nullable(); // ambient, chilled, frozen
        $table->enum('status', ['available', 'occupied', 'reserved', 'maintenance'])->default('available');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_bins');
    }
};
