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
        Schema::table('users', function (Blueprint $table) {
            $table->string('id_card_file')->nullable();
            $table->string('birth_certificate_file')->nullable();
            $table->string('diploma_file')->nullable();
            $table->string('ifu_file')->nullable();
            $table->string('rib_file')->nullable();

            // champs extraits
            $table->string('id_card_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
