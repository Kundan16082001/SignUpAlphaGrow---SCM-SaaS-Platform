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
    Schema::create('work_orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->string('wo_no')->unique();
        $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        $table->string('product_name');
        $table->foreignId('bom_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('machine_id')->nullable()->constrained()->nullOnDelete();
        $table->integer('target_qty');
        $table->integer('completed_qty')->default(0);
        $table->integer('scrap_qty')->default(0);
        $table->enum('status', ['planned', 'in_progress', 'completed', 'on_hold', 'cancelled'])->default('planned');
        $table->date('start_date')->nullable();
        $table->date('deadline');
        $table->integer('priority')->default(2); // 1=High, 2=Medium, 3=Low
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
