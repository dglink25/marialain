<?php

// database/migrations/xxxx_xx_xx_add_validation_to_students_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('students', function (Blueprint $table) {
            $table->boolean('validated')->default(false); // Secrétaire valide
            $table->decimal('registration_fee', 10, 2)->nullable(); // montant payé à l'inscription
        });
    }

    public function down(): void {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['validated', 'registration_fee']);
        });
    }
};
