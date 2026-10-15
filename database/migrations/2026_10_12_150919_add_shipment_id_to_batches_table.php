   <?php

   use Illuminate\Database\Migrations\Migration;
   use Illuminate\Database\Schema\Blueprint;
   use Illuminate\Support\Facades\Schema;

   return new class extends Migration
   {
       public function up(): void
       {
           Schema::table('batches', function (Blueprint $table) {
               // Add the foreign key column
               $table->foreignId('shipment_id')->nullable()->constrained('shipments')->nullOnDelete();
           });
       }

       public function down(): void
       {
           Schema::table('batches', function (Blueprint $table) {
               $table->dropForeign(['shipment_id']);
               $table->dropColumn('shipment_id');
           });
       }
   };