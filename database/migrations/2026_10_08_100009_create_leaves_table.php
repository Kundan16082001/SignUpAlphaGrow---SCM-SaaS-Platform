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
    Schema::create('leaves', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
        $table->string('leave_no')->unique();
        $table->enum('type', ['casual', 'sick', 'privilege', 'comp_off'])->default('casual');
        $table->date('from_date');
        $table->date('to_date');
        $table->decimal('days', 5, 2);
        $table->text('reason')->nullable();
        $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
