<?php

// database/migrations/2025_10_08_000006_create_students_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->string('nim', 20)->primary(); // Nomor Induk Mahasiswa
            $table->string('name');
            $table->string('email')->unique();
            $table->string('program_study')->nullable(); // Prodi
            $table->string('faculty')->nullable(); // Fakultas
            $table->string('semester')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
