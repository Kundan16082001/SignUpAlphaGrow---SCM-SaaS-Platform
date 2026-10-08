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
    Schema::create('expenses', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->string('expense_no')->unique();
        $table->foreignId('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
        $table->string('vendor')->nullable();
        $table->decimal('amount', 12, 2);
        $table->decimal('gst', 12, 2)->default(0);
        $table->decimal('tds', 12, 2)->default(0);
        $table->decimal('total', 12, 2);
        $table->string('payment_mode')->default('cash');
        $table->enum('status', ['pending', 'approved', 'paid', 'rejected'])->default('pending');
        $table->string('bill_attachment')->nullable();
        $table->text('description')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
