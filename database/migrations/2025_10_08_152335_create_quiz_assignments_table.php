<?php

// database/migrations/2025_10_08_000012_create_quiz_assignments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->string('student_nim', 20);
            $table->foreign('student_nim')->references('nim')->on('students')->onDelete('cascade');
            $table->json('question_ids'); // Array ID soal yang diberikan
            $table->timestamps();

            $table->unique(['quiz_id', 'student_nim']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_assignments');
    }
};
