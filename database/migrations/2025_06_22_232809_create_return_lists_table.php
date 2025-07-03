<?php

use App\Models\Customer;
use App\Models\Order;
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
        Schema::create('return_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Customer::class);
            $table->string('invoiceno');
            $table->date('sell_date');
            $table->float('discount')->nullable()->default(0);
            $table->float('receivable')->nullable()->default(0);
            $table->float('total_no_discount')->nullable()->default(0);
            $table->float('profit')->nullable()->default(0);
            $table->float('paid')->nullable()->default(0);
            $table->float('due')->nullable()->default(0);

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
        Schema::dropIfExists('return_lists');
    }
};
