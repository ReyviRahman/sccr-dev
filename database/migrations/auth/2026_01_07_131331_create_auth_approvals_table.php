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
        Schema::create('auth_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('module_code', 10);
            $table->string('action', 20); // UPDATE, DELETE
            $table->string('data_ref'); // inv_items:123
            $table->foreignId('requested_by')->constrained('auth_users');
            $table->foreignId('approved_by')->nullable()->constrained('auth_users');
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_approvals');
    }
};
