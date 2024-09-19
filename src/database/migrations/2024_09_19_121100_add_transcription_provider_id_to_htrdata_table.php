<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('HtrData', function (Blueprint $table) {

            $table
                ->unsignedSmallInteger('TranscriptionProviderId')
                ->after('EuropeanaAnnotationId')
                ->nullable();

            $table->foreign('TranscriptionProviderId')
                  ->references('TranscriptionProviderId')
                  ->on('TranscriptionProvider')
                  ->nullOnDelete();
        });

        DB::table('HtrData')->update(['TranscriptionProviderId' => 1]);
    }

    public function down()
    {
        Schema::table('HtrData', function (Blueprint $table) {
            $table->dropForeign(['TranscriptionProviderId']);
            $table->dropColumn('TranscriptionProviderId');
        });
    }
};
