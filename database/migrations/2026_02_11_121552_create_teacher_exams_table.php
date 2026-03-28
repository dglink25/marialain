<?php
// database/migrations/2026_02_11_000001_create_teacher_exams_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('teacher_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users');
            $table->foreignId('class_id')->constrained('classes');
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->enum('trimestre', [1, 2, 3]);
            $table->enum('type', ['interrogation', 'devoir']);
            $table->unsignedTinyInteger('numero_evaluation');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('file_url');
            $table->string('file_name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('teacher_exams');
    }
};