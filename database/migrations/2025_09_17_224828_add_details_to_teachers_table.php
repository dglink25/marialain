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
            $table->string('gender')->nullable(); 
            $table->string('phone')->nullable();
            $table->string('marital_status')->nullable(); 
            $table->string('address')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('nationality')->nullable();
            $table->string('id_card')->nullable();
            $table->string('birth_certificate')->nullable();
            $table->string('diploma')->nullable();
            $table->string('ifu_number')->nullable();
            $table->string('ifu')->nullable();
            $table->string('rib')->nullable();
            $table->string('rib_document')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'gender','phone','marital_status','address','birth_date','birth_place',
                'nationality','id_card','birth_certificate','diploma','ifu_number','ifu','rib','rib_document'
            ]);
        });
    }

};
