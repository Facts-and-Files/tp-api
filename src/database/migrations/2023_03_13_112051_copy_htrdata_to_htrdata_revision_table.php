<?php

use Illuminate\Database\Migrations\Migration;
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
        DB::statement('DELETE FROM HtrData WHERE HtrStatus = "CREATED"');

        DB::statement('
            INSERT INTO HtrDataRevision (
                HtrDataId,
                UserId,
                TranscriptionData,
                TranscriptionText,
                Timestamp,
                LastUpdated
            )
            SELECT
                HtrDataId,
                UserId,
                TranscriptionData,
                TranscriptionText,
                Timestamp,
                LastUpdated
            FROM HtrData
            WHERE 1 = 1
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('TRUNCATE HtrDataRevision');
    }
};
