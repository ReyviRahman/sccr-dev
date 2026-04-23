<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('sccr_resto')->table('purchase_requests', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('sccr_resto')->table('purchase_requests', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
