<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {

            if (!Schema::hasColumn('students', 'registration_type')) {
                $table->string('registration_type')
                      ->nullable()
                      ->after('is_validated')
                      ->comment('Type d\'inscription: new ou re_registration');
            }

            if (!Schema::hasColumn('students', 'total_fees')) {
                $table->decimal('total_fees', 10, 2)
                      ->nullable()
                      ->after('registration_type')
                      ->comment('Total des frais');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {

            if (Schema::hasColumn('students', 'registration_type')) {
                $table->dropColumn('registration_type');
            }

            if (Schema::hasColumn('students', 'total_fees')) {
                $table->dropColumn('total_fees');
            }
        });
    }
};