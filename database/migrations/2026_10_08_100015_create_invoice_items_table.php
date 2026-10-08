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
    Schema::create('invoice_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
        $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
        $table->string('description');
        $table->string('hsn_code')->nullable();
        $table->integer('qty');
        $table->decimal('rate', 10, 2);
        $table->decimal('taxable_value', 12, 2);
        $table->decimal('gst_rate', 5, 2)->default(18);
        $table->decimal('cgst', 10, 2)->default(0);
        $table->decimal('sgst', 10, 2)->default(0);
        $table->decimal('igst', 10, 2)->default(0);
        $table->decimal('total', 12, 2);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
