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
        Schema::create('employee_job_title', function (Blueprint $table) {
            $table->id();

            // Ganti employee_id dengan employee_nip
            $table->string('nip', 20);
            $table->foreign('nip')
                ->references('nip')
                ->on('employees')
                ->onDelete('cascade');

            $table->foreignId('job_title_id')->constrained('job_titles')->onDelete('cascade');
            $table->foreignId('holding_id')->constrained('holdings')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_job_title');
    }
};
