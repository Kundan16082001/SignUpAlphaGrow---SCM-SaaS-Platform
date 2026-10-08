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
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete(); // Nullable for SuperAdmin
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->enum('wing', ['manufacturer', 'warehouse', 'distributor', 'system'])->default('system');
        $table->string('role')->default('staff'); // superadmin, ceo, manager, staff
        $table->enum('status', ['active', 'suspended'])->default('active');
        $table->rememberToken();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
