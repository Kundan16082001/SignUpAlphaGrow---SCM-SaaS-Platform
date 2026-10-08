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
    Schema::create('payrolls', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->string('payroll_no')->unique();
        $table->string('month'); // e.g., "2026-10"
        $table->integer('employee_count')->default(0);
        $table->decimal('total_gross', 14, 2)->default(0);
        $table->decimal('total_deductions', 14, 2)->default(0);
        $table->decimal('total_pf', 14, 2)->default(0);
        $table->decimal('total_esi', 14, 2)->default(0);
        $table->decimal('total_tds', 14, 2)->default(0);
        $table->decimal('total_net', 14, 2)->default(0);
        $table->enum('status', ['draft', 'processed', 'paid'])->default('draft');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
