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
    Schema::create('employees', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        $table->string('emp_code')->unique();
        $table->string('name');
        $table->date('doj');
        $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
        $table->string('designation')->nullable();
        $table->string('uan')->nullable();
        $table->string('esi_no')->nullable();
        $table->decimal('basic', 12, 2)->default(0);
        $table->decimal('hra', 12, 2)->default(0);
        $table->decimal('gross_salary', 12, 2)->default(0);
        $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
