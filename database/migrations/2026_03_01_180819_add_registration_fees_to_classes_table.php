<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration{
    public function up(): void {
        Schema::table('classes', function (Blueprint $table) {

            if (!Schema::hasColumn('classes', 'registration_fee')) {
                $table->decimal('registration_fee', 10, 2)
                      ->nullable()
                      ->after('school_fees')
                      ->comment('Frais d\'inscription');
            }

            if (!Schema::hasColumn('classes', 're_registration_fee')) {
                $table->decimal('re_registration_fee', 10, 2)
                      ->nullable()
                      ->after('registration_fee')
                      ->comment('Frais de réinscription');
            }
        });
    }

    public function down(): void {
        Schema::table('classes', function (Blueprint $table) {

            if (Schema::hasColumn('classes', 'registration_fee')) {
                $table->dropColumn('registration_fee');
            }

            if (Schema::hasColumn('classes', 're_registration_fee')) {
                $table->dropColumn('re_registration_fee');
            }
        });
    }
};