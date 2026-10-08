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
    Schema::create('return_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('return_id')->constrained()->cascadeOnDelete();
        $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
        $table->integer('qty')->default(0);
        $table->integer('accepted_qty')->default(0);
        $table->integer('rejected_qty')->default(0);
        $table->decimal('unit_price', 10, 2)->default(0);
        $table->decimal('refund_amount', 10, 2)->default(0);
        $table->enum('condition', ['good', 'damaged', 'defective', 'expired'])->default('good');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_items');
    }
};
