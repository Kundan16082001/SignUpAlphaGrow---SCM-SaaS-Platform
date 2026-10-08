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
    Schema::create('payroll_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
        $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
        $table->decimal('basic', 12, 2);
        $table->decimal('hra', 12, 2);
        $table->decimal('allowances', 12, 2)->default(0);
        $table->decimal('overtime', 12, 2)->default(0);
        $table->decimal('gross', 12, 2);
        $table->decimal('pf', 12, 2)->default(0);
        $table->decimal('esi', 12, 2)->default(0);
        $table->decimal('tds', 12, 2)->default(0);
        $table->decimal('net_pay', 12, 2);
        $table->integer('working_days')->default(26);
        $table->integer('lop_days')->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_details');
    }
};
