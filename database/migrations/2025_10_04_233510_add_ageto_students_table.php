<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Modifier la colonne age de integer vers decimal
            $table->decimal('age', 5, 2)->change(); 
            // 5 chiffres au total, dont 2 après la virgule
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Revenir à entier si rollback
            $table->integer('age')->change();
        });
    }
};
