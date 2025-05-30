<?php

use App\Enums\InvoiceLogoType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('email_address')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('logo')->nullable();
            $table->enum('invoice_logo_type', array_column(InvoiceLogoType::cases(), 'value'))->default(InvoiceLogoType::NAME->value);
            $table->integer('low_stock_quantity')->default(10);
            $table->foreignId('tenant_id')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
