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
    Schema::create('quality_checks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->string('qc_no')->unique();
        $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
        $table->string('inspector_name');
        $table->integer('sample_size')->default(0);
        $table->integer('defect_count')->default(0);
        $table->decimal('qc_score', 5, 2)->default(0);
        $table->enum('result', ['pass', 'fail', 'rework', 'pending'])->default('pending');
        $table->text('parameters')->nullable(); // JSON or text of checked parameters
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_checks');
    }
};
