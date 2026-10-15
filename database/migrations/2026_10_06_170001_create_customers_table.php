<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Traits\Syncable;

return new class extends Migration
{
    use Syncable;
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('customer_code')->unique();
            $table->string('name');
            $table->string('gstin')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->decimal('credit_limit', 10, 2)->default(0);
            $table->decimal('outstanding_amount', 10, 2)->default(0); // <-- ADDED THIS LINE
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};