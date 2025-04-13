<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Unit;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('product_code')->nullable();
            $table->foreignIdFor(Category::class);
            $table->foreignIdFor(Brand::class)->nullable();
            $table->foreignIdFor(Unit::class);
            $table->foreignId('sub_unit')->nullable();
            $table->integer('first_opening_stock')->nullable();
            $table->integer('second_opening_stock')->nullable();
            $table->float('sale_price');
            $table->float('purchase_cost');
            $table->text('product_details')->nullable();
            $table->string('product_image')->nullable();

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
        Schema::dropIfExists('products');
    }
};
