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
            $table->float('single_unit_sale_price');
            $table->float('single_unit_purchase_price');

            $table->float('purchased');
            $table->text('purchased_in_text')->nullable();

            $table->float('sold');
            $table->text('sold_in_text');

            $table->float('damaged');
            $table->text('damaged_in_text');

            $table->float('returned');
            $table->text('returned_in_text');

            $table->float('available_stock');
            $table->text('available_stock_in_text');

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
