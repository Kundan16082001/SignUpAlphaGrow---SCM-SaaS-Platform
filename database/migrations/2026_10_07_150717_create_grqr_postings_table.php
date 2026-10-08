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
    Schema::create('grqr_postings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->string('posting_no')->unique();
        $table->enum('type', ['gr', 'qr']); // Goods Receipt or Quality Receipt
        $table->foreignId('shipment_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
        $table->integer('expected_qty')->default(0);
        $table->integer('received_qty')->default(0);
        $table->integer('damaged_qty')->default(0);
        $table->enum('qc_result', ['pass', 'fail', 'pending'])->default('pending');
        $table->decimal('qc_score', 5, 2)->nullable();
        $table->enum('status', ['pending', 'posted', 'rejected'])->default('pending');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grqr_postings');
    }
};
