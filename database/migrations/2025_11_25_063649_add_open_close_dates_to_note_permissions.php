<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('note_permissions', function (Blueprint $table) {
            $table->dateTime('open_at')->nullable();
            $table->dateTime('close_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('note_permissions', function (Blueprint $table) {
            $table->dropColumn(['open_at', 'close_at']);
        });
    }

};
