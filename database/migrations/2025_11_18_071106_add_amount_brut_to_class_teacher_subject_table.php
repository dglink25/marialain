<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_teacher_subject', function (Blueprint $table) {
            $table->decimal('amount_brut', 10, 2)->default(0)->after('subject_id');
        });
    }

    public function down(): void
    {
        Schema::table('class_teacher_subject', function (Blueprint $table) {
            $table->dropColumn('amount_brut');
        });
    }
};
