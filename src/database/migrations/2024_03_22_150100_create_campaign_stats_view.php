<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            CREATE VIEW campaign_stats_view AS
            SELECT
                c.CampaignId,
                sc.StoryId,
                s.ItemId,
                s.UserId,
                SUM(CASE WHEN s.ScoreTypeId = 1 THEN s.Amount ELSE 0 END) AS Locations,
                SUM(CASE WHEN s.ScoreTypeId = 2 THEN s.Amount ELSE 0 END) AS ManualTranscriptions,
                SUM(CASE WHEN s.ScoreTypeId = 3 THEN s.Amount ELSE 0 END) AS Enrichments,
                SUM(CASE WHEN s.ScoreTypeId = 4 THEN s.Amount ELSE 0 END) AS Descriptions,
                SUM(CASE WHEN s.ScoreTypeId = 5 THEN s.Amount ELSE 0 END) AS HTRTranscriptions,
                ROUND(SUM(s.Amount * st.Rate) + 0.5, 0) AS Miles
            FROM
                Campaign c
            JOIN
                StoryCampaign sc ON sc.CampaignId = c.CampaignId
            JOIN
                Item i ON i.StoryId = sc.StoryId
            JOIN
                Score s ON s.ItemId = i.ItemId AND s.Timestamp >= c.Start AND s.Timestamp <= c.End
            JOIN
                ScoreType st ON st.ScoreTypeId = s.ScoreTypeId
            GROUP BY
                c.CampaignId, sc.StoryId, s.ItemId, s.UserId;
        ');
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS campaign_stats_view');
    }
};
