<?php

use App\HistoryTypeEnum;
use App\Models\Account;
use App\Models\Owner;
use App\Models\Purchase;
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
            $table->date('date');
            $table->float('amount');
            $table->enum('type', array_column(HistoryTypeEnum::cases(), 'value'));
            $table->text('note')->nullable();
            $table->foreignIdFor(Purchase::class)->nullable();

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
