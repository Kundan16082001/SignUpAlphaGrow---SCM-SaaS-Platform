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
    Schema::create('tickets', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->string('ticket_no')->unique();
        $table->string('customer_name');
        $table->string('subject');
        $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
        $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
        $table->unsignedBigInteger('assigned_to')->nullable();
        $table->text('description')->nullable();
        $table->text('resolution')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
