<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('damages', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class);
            $table->string('date');
            $table->text('note')->nullable();

            $table->integer('quantity_in_main_unit')->nullable();
            $table->integer('quantity_in_sub_unit')->nullable()->default(0);
            $table->integer('total_qty')->nullable()->default(0);
            $table->string('total_in_text')->nullable();

            $table->json('purchase_ids')->nullable();

            $table->foreignId('tenant_id')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('damages');
    }
};
