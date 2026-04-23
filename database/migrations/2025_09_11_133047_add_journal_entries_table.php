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
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->foreignId('currency_id')->after('employee_nip')->nullable()->constrained('currencies')->onDelete('set null');
            $table->decimal('exchange_rate', 20, 6)->nullable();       // Kurs saat transaksi
            $table->decimal('amount_foreign', 20, 2)->nullable();      // Nilai asli dalam mata uang asing
            $table->decimal('debit_local', 20, 2)->nullable();         // Nilai setelah konversi
            $table->decimal('credit_local', 20, 2)->nullable();
        });
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropColumn([
                'currency_id', 'exchange_rate', 'amount_foreign', 'debit_local',
                'credit_local',
            ]);
        });
        //
    }
};
