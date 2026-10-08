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
    Schema::create('leads', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->string('lead_code')->unique();
        $table->string('company_name');
        $table->string('contact_person')->nullable();
        $table->string('source')->nullable();
        $table->decimal('value', 12, 2)->default(0);
        $table->enum('status', ['new', 'contacted', 'quoted', 'won', 'lost'])->default('new');
        $table->unsignedBigInteger('assigned_to')->nullable();
        $table->date('follow_up_date')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
