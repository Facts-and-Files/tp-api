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
            $table->enum('TranscriptionSource', ['manual', 'htr'])->default('manual');
            $table
                ->enum('PersonRole', ['DocumentCreator', 'AddressedPerson', 'PersonMentioned'])
                ->after('Description')
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
        Schema::table('person', function (Blueprint $table) {
            $table->dropColumn('PersonRole');
        });
    }
};
