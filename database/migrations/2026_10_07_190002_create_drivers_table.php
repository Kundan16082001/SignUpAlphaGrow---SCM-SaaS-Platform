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
    Schema::create('drivers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->string('name');
        $table->string('license')->unique();
        $table->string('phone')->nullable();
        $table->string('vehicle_type')->nullable();
        $table->string('vehicle_no')->nullable();
        $table->integer('capacity')->default(0); // in kg
        $table->integer('experience_years')->default(0);
        $table->decimal('rating', 3, 2)->default(5.00);
        $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
