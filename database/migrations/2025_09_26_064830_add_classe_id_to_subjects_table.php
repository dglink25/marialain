<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('subjects', function (Blueprint $table) {
        $table->unsignedBigInteger('classe_id')->nullable()->after('id');

        $table->foreign('classe_id')
              ->references('id')->on('classes')
              ->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::table('subjects', function (Blueprint $table) {
        $table->dropForeign(['classe_id']);
        $table->dropColumn('classe_id');
    });
}

};
