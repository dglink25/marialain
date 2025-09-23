<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Timetables
        Schema::table('timetables', function (Blueprint $table) {
            if (!Schema::hasColumn('timetables', 'academic_year_id')) {
                $table->unsignedBigInteger('academic_year_id')->nullable()->after('id');
                $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            }
        });

        // Class-Teacher-Subject
        Schema::table('class_teacher_subject', function (Blueprint $table) {
            if (!Schema::hasColumn('class_teacher_subject', 'academic_year_id')) {
                $table->unsignedBigInteger('academic_year_id')->nullable()->after('id');
                $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            }
        });

        // Student payments
        Schema::table('student_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('student_payments', 'academic_year_id')) {
                $table->unsignedBigInteger('academic_year_id')->nullable()->after('id');
                $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            }
        });

        // Teacher invitations
        Schema::table('teacher_invitations', function (Blueprint $table) {
            if (!Schema::hasColumn('teacher_invitations', 'academic_year_id')) {
                $table->unsignedBigInteger('academic_year_id')->nullable()->after('id');
                $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        foreach (['timetables', 'class_teacher_subject', 'student_payments', 'teacher_invitations'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign([$table.'_academic_year_id_foreign']);
                $table->dropColumn('academic_year_id');
            });
        }
    }
};
