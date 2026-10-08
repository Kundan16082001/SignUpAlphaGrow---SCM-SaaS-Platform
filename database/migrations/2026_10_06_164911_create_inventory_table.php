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
    Schema::create('inventory', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
        $table->string('location')->nullable();
        $table->string('warehouse_name')->nullable();
        $table->string('bin_code')->nullable();
        $table->integer('qty')->default(0);
        $table->integer('reserved_qty')->default(0);
        $table->integer('damaged_qty')->default(0);
        $table->enum('status', ['in_stock', 'quarantine', 'transit'])->default('in_stock');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
