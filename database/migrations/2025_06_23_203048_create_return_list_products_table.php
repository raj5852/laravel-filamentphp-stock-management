<?php

use App\Models\Product;
use App\Models\ReturnList;
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
        Schema::create('return_list_products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ReturnList::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Product::class);
            $table->string('total_in_text');
            $table->integer('total_qty');
            $table->float('purchase_cost');

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
        Schema::dropIfExists('return_list_products');
    }
};
