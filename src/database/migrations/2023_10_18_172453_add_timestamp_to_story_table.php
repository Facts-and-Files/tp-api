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
        Schema::table('Story', function (Blueprint $table) {
            $table
                ->dateTime('Timestamp')
                ->after('HasHtr')
                ->nullable();
        });

        DB::table('Story')->update(['Timestamp' => DB::raw('LastUpdated')]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Story', function (Blueprint $table) {
            $table->dropColumn('Timestamp');
        });
    }
};
