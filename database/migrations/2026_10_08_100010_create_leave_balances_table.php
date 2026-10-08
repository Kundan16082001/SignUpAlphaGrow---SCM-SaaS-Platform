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
    Schema::create('leave_balances', function (Blueprint $table) {
        $table->id();
        $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
        $table->year('year');
        $table->decimal('casual_earned', 5, 2)->default(12);
        $table->decimal('casual_used', 5, 2)->default(0);
        $table->decimal('sick_earned', 5, 2)->default(10);
        $table->decimal('sick_used', 5, 2)->default(0);
        $table->decimal('privilege_earned', 5, 2)->default(15);
        $table->decimal('privilege_used', 5, 2)->default(0);
        $table->timestamps();
        $table->unique(['employee_id', 'year']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
