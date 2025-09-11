<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            if (!Schema::hasColumn('invitations', 'entity')) {
                $table->string('entity')->after('academic_year_id');
            }
        });

        Schema::table('classes', function (Blueprint $table) {
            if (!Schema::hasColumn('classes', 'entity')) {
                $table->string('entity')->after('academic_year_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn('entity');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('entity');
        });
    }
};
