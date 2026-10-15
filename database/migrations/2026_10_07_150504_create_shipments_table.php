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
    Schema::create('shipments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->string('shipment_no')->unique();
        $table->enum('direction', ['inbound', 'outbound', 'return']);
        $table->string('origin');
        $table->string('destination');
        $table->foreignId('carrier_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
        $table->decimal('freight_cost', 10, 2)->default(0);
        $table->date('eta_date')->nullable();
        $table->enum('status', ['pending', 'in_transit', 'delivered', 'delayed', 'cancelled'])->default('pending');
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
