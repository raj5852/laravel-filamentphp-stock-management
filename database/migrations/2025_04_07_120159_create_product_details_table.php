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
        Schema::create('product_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class)->constrained()->onDelete('cascade');
            $table->integer('single_unit_sale_price');
            $table->integer('single_unit_purchase_price');

            $table->integer('purchased');
            $table->string('purchased_in_text')->nullable();

            $table->integer('sold');
            $table->string('sold_in_text');

            $table->integer('damaged');
            $table->string('damaged_in_text');

            $table->integer('returned');
            $table->string('returned_in_text');

            $table->integer('available_stock');
            $table->string('available_stock_in_text');

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
        Schema::dropIfExists('product_details');
    }
};
