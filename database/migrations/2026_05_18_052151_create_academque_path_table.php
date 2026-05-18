<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_academic_records', function (Blueprint $table) {
            $table->id();

            // ── Clés de référence ────────────────────────────────────────────
            $table->foreignId('student_id')
                  ->constrained('students')
                  ->onDelete('cascade');

            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')
                  ->onDelete('cascade');

            $table->foreignId('class_id')
                  ->constrained('classes')
                  ->onDelete('cascade');

            $table->foreignId('entity_id')
                  ->constrained('entities')
                  ->onDelete('cascade');

            // ── Snapshot des données élève au moment de l'archivage ──────────
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();
            $table->enum('gender', ['M', 'F'])->nullable();
            $table->string('num_educ')->nullable();
            $table->string('parent_full_name')->nullable();
            $table->string('parent_email')->nullable();
            $table->string('parent_phone')->nullable();
            $table->string('registration_type')->nullable(); // new | re_registration
            $table->decimal('total_fees', 10, 2)->nullable();
            $table->decimal('amount_paid', 10, 2)->nullable();

            // ── Résultats académiques snapshot ───────────────────────────────
            $table->decimal('moy_trimestre_1', 5, 2)->nullable();
            $table->decimal('moy_trimestre_2', 5, 2)->nullable();
            $table->decimal('moy_trimestre_3', 5, 2)->nullable();
            $table->decimal('moy_annuelle', 5, 2)->nullable();
            $table->integer('rang_annuel')->nullable();
            $table->enum('statut_deliberation', ['passed', 'repeated', 'pending'])
                  ->default('pending');

            // ── Classe de destination après délibération ─────────────────────
            $table->foreignId('next_class_id')
                  ->nullable()
                  ->constrained('classes')
                  ->onDelete('set null');

            $table->foreignId('next_academic_year_id')
                  ->nullable()
                  ->constrained('academic_years')
                  ->onDelete('set null');

            // ── Métadonnées ──────────────────────────────────────────────────
            $table->boolean('is_validated')->default(true);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            // Un seul enregistrement par élève par année
            $table->unique(['student_id', 'academic_year_id'], 'sar_student_year_unique');

            // Index utiles
            $table->index('academic_year_id');
            $table->index('class_id');
            $table->index('entity_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_academic_records');
    }
};