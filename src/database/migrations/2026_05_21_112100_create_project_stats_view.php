<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
    public function up(): void
    {
        DB::statement('
            CREATE VIEW project_stats_view AS
            SELECT
                p.ProjectId,
                st.StoryId,
                s.ItemId,
                s.UserId,
                SUM(CASE WHEN s.ScoreTypeId = 1 THEN s.Amount ELSE 0 END) AS Locations,
                SUM(CASE WHEN s.ScoreTypeId = 2 THEN s.Amount ELSE 0 END) AS ManualTranscriptions,
                SUM(CASE WHEN s.ScoreTypeId = 3 THEN s.Amount ELSE 0 END) AS Enrichments,
                SUM(CASE WHEN s.ScoreTypeId = 4 THEN s.Amount ELSE 0 END) AS Descriptions,
                SUM(CASE WHEN s.ScoreTypeId = 5 THEN s.Amount ELSE 0 END) AS HTRTranscriptions,
                ROUND(SUM(s.Amount * stt.Rate) + 0.5, 0) AS Miles
            FROM
                Project p
            JOIN
                Story st ON st.ProjectId = p.ProjectId
            JOIN
                Item i ON i.StoryId = st.StoryId
            JOIN
                Score s ON s.ItemId = i.ItemId
            JOIN
                ScoreType stt ON stt.ScoreTypeId = s.ScoreTypeId
            GROUP BY
                p.ProjectId, st.StoryId, s.ItemId, s.UserId;
        ');
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS project_stats_view');
    }
};
