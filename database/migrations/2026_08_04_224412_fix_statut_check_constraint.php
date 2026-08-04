<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE deliberation_students DROP CONSTRAINT deliberation_students_status_check');

        DB::statement("ALTER TABLE deliberation_students
            ADD CONSTRAINT deliberation_students_status_check
            CHECK (status IN ('pending', 'passed', 'repeated', 'graduated'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE deliberation_students DROP CONSTRAINT deliberation_students_status_check');

        DB::statement("ALTER TABLE deliberation_students
            ADD CONSTRAINT deliberation_students_status_check
            CHECK (status IN ('pending', 'passed', 'repeated'))");
    }
};