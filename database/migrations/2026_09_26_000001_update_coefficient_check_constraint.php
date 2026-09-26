<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Supprimer l'ancienne contrainte CHECK (coefficient <= 10)
        // et la remplacer par (coefficient <= 20)
        DB::statement('ALTER TABLE class_teacher_subject DROP CONSTRAINT IF EXISTS class_teacher_subject_coefficient_check');
        DB::statement('ALTER TABLE class_teacher_subject ADD CONSTRAINT class_teacher_subject_coefficient_check CHECK (coefficient >= 1 AND coefficient <= 20)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE class_teacher_subject DROP CONSTRAINT IF EXISTS class_teacher_subject_coefficient_check');
        DB::statement('ALTER TABLE class_teacher_subject ADD CONSTRAINT class_teacher_subject_coefficient_check CHECK (coefficient >= 1 AND coefficient <= 10)');
    }
};
