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
    Schema::create('dealers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->string('dealer_code')->unique();
        $table->string('name');
        $table->string('region')->nullable();
        $table->integer('total_orders')->default(0);
        $table->decimal('total_business', 14, 2)->default(0);
        $table->decimal('credit_limit', 12, 2)->default(0);
        $table->decimal('outstanding', 12, 2)->default(0);
        $table->enum('status', ['active', 'inactive'])->default('active');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dealers');
    }
};
