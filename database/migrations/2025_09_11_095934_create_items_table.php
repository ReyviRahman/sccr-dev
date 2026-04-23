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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('holding_id')->constrained('holdings')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('type', ['goods', 'service']);
            $table->string('unit');
            $table->decimal('purchase_price', 20, 2)->default(0);
            $table->decimal('selling_price', 20, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
