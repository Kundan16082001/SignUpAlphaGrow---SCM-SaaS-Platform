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
    Schema::create('boms', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->string('bom_no')->unique();
        $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        $table->string('product_name');
        $table->string('product_sku');
        $table->decimal('unit_cost', 10, 2)->default(0);
        $table->integer('components_count')->default(0);
        $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
        $table->integer('version')->default(1);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boms');
    }
};
