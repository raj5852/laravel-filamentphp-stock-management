<?php

use App\HistoryTypeEnum;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Owner;
use App\Models\Purchase;
use App\Models\Supplier;
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
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Account::class);
            $table->foreignIdFor(Owner::class)->nullable();
            $table->foreignIdFor(Purchase::class)->nullable();
            $table->foreignIdFor(Order::class)->nullable();
            $table->foreignIdFor(Customer::class)->nullable();
            $table->foreignIdFor(Supplier::class)->nullable();

            $table->date('date');
            $table->float('amount');
            $table->enum('type', HistoryTypeEnum::toArray());
            $table->text('note')->nullable();
            // $table->enum('wallet_payment');

            $table->float('total_amount')->nullable()->default(0);

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
        Schema::dropIfExists('histories');
    }
};
