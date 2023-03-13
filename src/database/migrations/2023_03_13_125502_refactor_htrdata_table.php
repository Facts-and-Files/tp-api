<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('HtrData', function (Blueprint $table) {
            $table->dropUnique('htrdata_processid_unique');
            $table->dropIndex('htrdata_processid_index');

            DB::statement('ALTER TABLE HtrData CHANGE COLUMN ProcessId HtrProcessId int(11)');
            DB::statement('ALTER TABLE HtrData CHANGE COLUMN HtrId HtrModelId int(11)');

            $table->unique('HtrProcessId');
            $table->index('HtrProcessId');
            // $table->dropColumn(['UserId', 'TranscriptionData', 'TranscriptionText']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('HtrData', function (Blueprint $table) {
            $table->dropUnique('htrdata_htrprocessid_unique');
            $table->dropIndex('htrdata_htrprocessid_index');

            DB::statement('ALTER TABLE HtrData CHANGE COLUMN HtrProcessId ProcessId int(11)');
            DB::statement('ALTER TABLE HtrData CHANGE COLUMN HtrModelId HtrId int(11)');

            $table->unique('ProcessId');
            $table->index('ProcessId');
        });
    }
};
