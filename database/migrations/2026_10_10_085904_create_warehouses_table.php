<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();           // e.g., WH-MUM-001
            $table->string('name');                     // e.g., Mumbai Main Warehouse
            $table->string('type')->default('main');    // main, branch, cold_storage, hazmat, return
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            $table->string('country')->default('India');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('manager_name')->nullable();
            $table->string('manager_phone')->nullable();
            $table->string('manager_email')->nullable();
            $table->integer('total_area_sqft')->default(0);
            $table->integer('pallet_capacity')->default(0);
            $table->integer('shelf_capacity')->default(0);
            $table->integer('cold_capacity')->default(0);
            $table->integer('hazmat_capacity')->default(0);
            $table->decimal('max_weight_tons', 8, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('warehouses');
    }
};