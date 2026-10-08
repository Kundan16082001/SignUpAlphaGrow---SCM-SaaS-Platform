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
    Schema::create('vehicles', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->string('reg_no')->unique();
        $table->string('type')->nullable(); // Truck, Van, Container
        $table->string('model')->nullable();
        $table->integer('capacity')->default(0); // in kg
        $table->string('fuel_type')->nullable();
        $table->year('year')->nullable();
        $table->date('insurance_expiry')->nullable();
        $table->date('fitness_expiry')->nullable();
        $table->enum('status', ['available', 'in_transit', 'maintenance'])->default('available');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
