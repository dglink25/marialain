<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration{
    public function up(): void {
        DB::statement('ALTER TABLE student_academic_records DROP CONSTRAINT student_academic_records_statut_deliberation_check');

        DB::statement("ALTER TABLE student_academic_records
            ADD CONSTRAINT student_academic_records_statut_deliberation_check
            CHECK (statut_deliberation IN ('pending', 'passed', 'repeated', 'graduated'))");
    }

    public function down(): void  {
        DB::statement('ALTER TABLE student_academic_records DROP CONSTRAINT student_academic_records_statut_deliberation_check');

        DB::statement("ALTER TABLE student_academic_records
            ADD CONSTRAINT student_academic_records_statut_deliberation_check
            CHECK (statut_deliberation IN ('pending', 'passed', 'repeated'))");
    }
};