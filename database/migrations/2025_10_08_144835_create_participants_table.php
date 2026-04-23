<?php

// database/migrations/2025_10_08_000004_create_participants_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->string('student_nim', 20); // Relasi ke students.nim
            $table->foreignId('webinar_id')->constrained('webinars')->onDelete('cascade');
            $table->enum('role', ['viewer', 'moderator', 'host'])->default('viewer');
            $table->dateTime('joined_at')->nullable();
            $table->dateTime('left_at')->nullable();
            $table->timestamps();

            $table->foreign('student_nim')->references('nim')->on('students')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
