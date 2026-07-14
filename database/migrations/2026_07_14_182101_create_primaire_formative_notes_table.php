<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('primaire_formative_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')
                  ->constrained('primaire_formative_evaluations')
                  ->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->decimal('note', 5, 2)->nullable(); // null = absent/non noté
            $table->timestamps();

            $table->unique(['evaluation_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('primaire_formative_notes');
    }
};
