<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->integer('tranche')->comment('Numéro de tranche, 1, 2 ou 3');
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->string('receipt')->nullable()->comment('Nom du fichier PDF du reçu');
            $table->timestamps();
        });

        // Ajouter colonne "school_fees_paid" et "fully_paid" à students
        Schema::table('students', function (Blueprint $table) {
            $table->decimal('school_fees_paid', 10, 2)->default(0);
            $table->boolean('fully_paid')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_payments');

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['school_fees_paid','fully_paid']);
        });
    }
};
