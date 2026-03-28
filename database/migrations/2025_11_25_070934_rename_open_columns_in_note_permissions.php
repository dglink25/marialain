<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('note_permissions', function (Blueprint $table) {
            $table->renameColumn('open_at', 'opens_at');
            $table->renameColumn('close_at', 'closes_at');
        });
    }

    public function down()
    {
        Schema::table('note_permissions', function (Blueprint $table) {
            $table->renameColumn('opens_at', 'open_at');
            $table->renameColumn('closes_at', 'close_at');
        });
    }

};
