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
    Schema::create('movements', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
        $table->foreignId('shipment_id')->nullable()->constrained()->nullOnDelete();
        $table->string('action'); // e.g., 'produced', 'dispatched', 'received', 'returned'
        $table->string('from_location')->nullable();
        $table->string('to_location')->nullable();
        $table->string('from_wing')->nullable(); // manufacturer, warehouse, distributor
        $table->string('to_wing')->nullable();
        $table->integer('qty')->default(0);
        $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
        $table->enum('status', ['pending', 'in_transit', 'completed'])->default('completed');
        $table->timestamps();
        $table->index(['batch_id', 'tenant_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
