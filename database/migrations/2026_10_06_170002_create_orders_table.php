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
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
        $table->string('order_no')->unique();
        $table->enum('type', ['inbound', 'outbound']);
        $table->decimal('subtotal', 10, 2)->default(0);
        $table->decimal('tax', 10, 2)->default(0);
        $table->decimal('total', 10, 2)->default(0);
        $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
        $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
        $table->date('order_date');
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
        Schema::dropIfExists('order_items');
    }
};
