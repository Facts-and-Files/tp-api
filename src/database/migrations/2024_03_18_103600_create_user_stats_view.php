<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // seed new ScoreTypeId
        DB::table('ScoreType')->insertOrIgnore([
            [
                'ScoreTypeId' => 5,
                'Name' => 'HTR-Transcription',
                'Rate' => 0.0033
            ]
        ]);

        DB::statement('
            CREATE VIEW user_stats_view AS
            SELECT
                s.UserId,
                COUNT(DISTINCT s.ItemId) AS Items,
                SUM(CASE WHEN s.ScoreTypeId = 1 THEN s.Amount ELSE 0 END) AS Locations,
                SUM(CASE WHEN s.ScoreTypeId = 2 THEN s.Amount ELSE 0 END) AS ManualTranscriptions,
                SUM(CASE WHEN s.ScoreTypeId = 3 THEN s.Amount ELSE 0 END) AS Enrichments,
                SUM(CASE WHEN s.ScoreTypeId = 4 THEN s.Amount ELSE 0 END) AS Descriptions,
                SUM(CASE WHEN s.ScoreTypeId = 5 THEN s.Amount ELSE 0 END) AS HTRTranscriptions,
                ROUND(SUM(s.Amount * st.Rate) + 0.5, 0) AS Miles
            FROM
                Score s
            JOIN
                ScoreType st ON s.ScoreTypeId = st.ScoreTypeId
            GROUP BY
                UserId;
        ');
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS user_stats_view');
    }
};
