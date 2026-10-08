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
    Schema::create('machines', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->string('machine_code')->unique();
        $table->string('name');
        $table->string('type')->nullable();
        $table->enum('status', ['operational', 'maintenance', 'breakdown', 'idle'])->default('operational');
        $table->decimal('efficiency', 5, 2)->default(100);
        $table->date('next_maintenance')->nullable();
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
