<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_teacher_subject', function (Blueprint $table) {
            // Supprimer l'ancienne contrainte unique (class_id, teacher_id, subject_id)
            $table->dropUnique('class_teacher_subject_class_id_teacher_id_subject_id_unique');

            // Nouvelle contrainte unique incluant academic_year_id :
            // un même enseignant peut enseigner la même matière dans la même classe
            // mais sur des années académiques différentes
            $table->unique(
                ['class_id', 'teacher_id', 'subject_id', 'academic_year_id'],
                'cts_class_teacher_subject_year_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('class_teacher_subject', function (Blueprint $table) {
            $table->dropUnique('cts_class_teacher_subject_year_unique');
            $table->unique(
                ['class_id', 'teacher_id', 'subject_id'],
                'class_teacher_subject_class_id_teacher_id_subject_id_unique'
            );
        });
    }
};
