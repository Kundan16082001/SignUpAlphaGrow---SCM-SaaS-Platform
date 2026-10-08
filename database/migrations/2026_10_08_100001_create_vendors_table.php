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
    Schema::create('vendors', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->string('vendor_code')->unique();
        $table->string('name');
        $table->string('gstin')->nullable();
        $table->string('contact_person')->nullable();
        $table->string('phone')->nullable();
        $table->string('bank_name')->nullable();
        $table->string('account_number')->nullable();
        $table->string('ifsc_code')->nullable();
        $table->decimal('credit_limit', 12, 2)->default(0);
        $table->decimal('rating', 3, 2)->default(5.00);
        $table->enum('status', ['active', 'inactive'])->default('active');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
