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
    Schema::create('returns', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->string('return_no')->unique();
        $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
        $table->string('origin')->nullable();
        $table->string('destination')->nullable();
        $table->string('reason')->nullable(); // Defect, QC Fail, Damage, Recall, Expired
        $table->integer('qty')->default(0);
        $table->decimal('value', 12, 2)->default(0);
        $table->decimal('refund_amount', 12, 2)->default(0);
        $table->enum('status', ['initiated', 'in_transit', 'received', 'inspected', 'approved', 'rejected', 'completed'])->default('initiated');
        $table->enum('direction', ['distributor_to_warehouse', 'warehouse_to_manufacturer', 'customer_to_distributor'])->default('customer_to_distributor');
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
