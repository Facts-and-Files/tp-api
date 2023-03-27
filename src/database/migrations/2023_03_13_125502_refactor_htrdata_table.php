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

            $table->dropForeign('htrdata_userid_foreign');
            $table->dropIndex('htrdata_userid_index');

            DB::statement('ALTER TABLE HtrData CHANGE COLUMN ProcessId HtrProcessId int(11)');
            DB::statement('ALTER TABLE HtrData CHANGE COLUMN HtrId HtrModelId int(11)');

            $table->unique('HtrProcessId');
            $table->index('HtrProcessId');

            $table->index('HtrStatus');

            $table->dropColumn(['UserId', 'TranscriptionData', 'TranscriptionText']);

            $table->unsignedBigInteger('EuropeanaAnnotationId')->after('HtrStatus')->nullable();
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

            $table->dropIndex('htrdata_htrstatus_index');

            DB::statement('ALTER TABLE HtrData CHANGE COLUMN HtrProcessId ProcessId int(11)');
            DB::statement('ALTER TABLE HtrData CHANGE COLUMN HtrModelId HtrId int(11)');

            $table->mediumtext('TranscriptionText')->after('HtrStatus')->nullable();
            $table->mediumtext('TranscriptionData')->after('HtrStatus')->nullable();
            $table->integer('UserId')->after('ItemId')->nullable();

            $table->unique('ProcessId');
            $table->index('ProcessId');

            $table->index('UserId');
            $table->foreign('UserId')
                  ->references('UserId')
                  ->on('User')
                  ->restrictOnDelete()
                  ->restrictOnUpdate();

            $table->dropColumn('EuropeanaAnnotationId');
        });
    }
};
