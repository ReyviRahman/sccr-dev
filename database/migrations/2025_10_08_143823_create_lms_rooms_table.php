<?php

// database/migrations/2025_10_08_000002_create_lms_rooms_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Nama room unik
            $table->string('lecturer_nip', 20); // Relasi ke employees.nip
            $table->string('kurikulum');
            $table->string('semester');
            $table->integer('max_participants')->default(10000);
            $table->boolean('is_active')->default(true);
            $table->string('token')->nullable(); // Token akses dari SIAKAD
            $table->timestamps();

            $table->foreign('lecturer_nip')->references('nip')->on('employees')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_rooms');
    }
};
