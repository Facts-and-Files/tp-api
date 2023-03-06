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
        Schema::table('AutoEnrichment', function (Blueprint $table) {
            $table
                ->string('Comment', 1000)
                ->after('StoryId')
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
        Schema::table('AutoEnrichment', function (Blueprint $table) {
            $table->dropColumn('Comment');
        });
    }
};
