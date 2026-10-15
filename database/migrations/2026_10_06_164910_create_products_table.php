<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Traits\Syncable;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    use Syncable;
    public function up()
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->string('sku')->unique();
        $table->string('name');
        $table->string('category')->nullable();
        $table->string('unit')->default('PCS');
        $table->decimal('gst_rate', 5, 2)->default(18.00);
        $table->string('hsn_code')->nullable();
        $table->decimal('price', 10, 2)->default(0);
        $table->decimal('cost_price', 10, 2)->default(0);
        $table->integer('stock_qty')->default(0);
        $table->integer('reorder_level')->default(10);
        $table->enum('status', ['active', 'inactive'])->default('active');
        $table->timestamps();
        $table->boolean('is_synced')->default(false);
$table->timestamp('last_synced_at')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
