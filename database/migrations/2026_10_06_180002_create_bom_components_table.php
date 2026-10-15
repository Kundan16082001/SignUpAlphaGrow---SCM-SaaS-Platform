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
    Schema::create('bom_components', function (Blueprint $table) {
        $table->id();
        $table->foreignId('bom_id')->constrained()->cascadeOnDelete();
        $table->string('component_sku');
        $table->string('component_name');
        $table->decimal('qty_required', 10, 3);
        $table->string('unit')->default('PCS');
        $table->decimal('unit_cost', 10, 2)->default(0);
        $table->decimal('total_cost', 10, 2)->default(0);
        $table->decimal('wastage_percentage', 5, 2)->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_components');
    }
};
