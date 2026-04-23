<?php

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
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('holding_id')->constrained('holdings')->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->date('date');
            $table->string('customer_name');
            $table->decimal('total_amount', 20, 2);
            $table->enum('status', ['draft', 'posted', 'paid'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoices');
    }
};
