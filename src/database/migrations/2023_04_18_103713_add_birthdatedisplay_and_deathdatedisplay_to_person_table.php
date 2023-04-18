<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Person', function (Blueprint $table) {
            $table
                ->string('BirthDateDisplay', 255)
                ->after('BirthDate')
                ->nullable();
            $table
                ->string('DeathDateDisplay', 255)
                ->after('DeathDate')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Person', function (Blueprint $table) {
            $table->dropColumn('BirthDateDisplay');
            $table->dropColumn('DeathDateDisplay');
        });
    }
};
