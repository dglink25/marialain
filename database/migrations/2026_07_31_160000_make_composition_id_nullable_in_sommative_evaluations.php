<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('primaire_sommative_evaluations', function (Blueprint $table) {
            $table->unsignedBigInteger('composition_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('primaire_sommative_evaluations', function (Blueprint $table) {
            $table->unsignedBigInteger('composition_id')->nullable(false)->change();
        });
    }
};
