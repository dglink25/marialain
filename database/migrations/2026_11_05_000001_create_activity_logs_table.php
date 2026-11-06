<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_role')->nullable();
            $table->string('action');             // create, update, delete, login, logout, etc.
            $table->string('model')->nullable();   // Classe, Student, User, etc.
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description');           // texte lisible
            $table->json('old_data')->nullable();  // données avant
            $table->json('new_data')->nullable();  // données après
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device')->nullable();  // Mobile, Desktop, Tablet
            $table->string('url')->nullable();
            $table->string('method')->nullable();  // GET, POST, PUT, DELETE
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['model', 'model_id']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
