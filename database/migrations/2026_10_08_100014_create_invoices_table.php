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
    Schema::create('invoices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->string('invoice_no')->unique();
        $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
        $table->enum('type', ['b2b', 'b2c', 'export'])->default('b2b');
        $table->decimal('cgst', 12, 2)->default(0);
        $table->decimal('sgst', 12, 2)->default(0);
        $table->decimal('igst', 12, 2)->default(0);
        $table->decimal('total', 12, 2)->default(0);
        $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
        $table->date('due_date')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
