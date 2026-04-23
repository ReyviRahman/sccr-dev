<?php

// database/migrations/2025_10_08_000013_create_quiz_answers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->string('student_nim', 20);
            $table->foreign('student_nim')->references('nim')->on('students')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('quiz_questions')->onDelete('cascade');
            $table->text('answer');
            $table->float('score')->nullable(); // Nilai per soal
            $table->timestamps();

            $table->unique(['quiz_id', 'student_nim', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
};
