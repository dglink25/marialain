<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_academic_records', function (Blueprint $table) {
            if (!Schema::hasColumn('student_academic_records', 'school_fees_snapshot')) {
                $table->decimal('school_fees_snapshot', 10, 2)->nullable()
                      ->after('total_fees')
                      ->comment('Frais scolarité de la classe au moment de l\'archivage');
            }
            if (!Schema::hasColumn('student_academic_records', 'registration_fee_snapshot')) {
                $table->decimal('registration_fee_snapshot', 10, 2)->nullable()
                      ->after('school_fees_snapshot')
                      ->comment('Frais inscription au moment de l\'archivage');
            }
            if (!Schema::hasColumn('student_academic_records', 're_registration_fee_snapshot')) {
                $table->decimal('re_registration_fee_snapshot', 10, 2)->nullable()
                      ->after('registration_fee_snapshot')
                      ->comment('Frais réinscription au moment de l\'archivage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_academic_records', function (Blueprint $table) {
            $table->dropColumnIfExists([
                'school_fees_snapshot',
                'registration_fee_snapshot',
                're_registration_fee_snapshot',
            ]);
        });
    }
};
