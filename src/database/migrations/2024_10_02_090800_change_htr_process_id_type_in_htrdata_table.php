<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('HtrData', function (Blueprint $table) {
            $table->string('HtrProcessId')->nullable()->unique(false)->change();
        });
    }

    public function down()
    {
        Schema::table('HtrData', function (Blueprint $table) {
            $table->integer('HtrProcessId')->nullable()->unique(true)->change();
        });
    }
};
