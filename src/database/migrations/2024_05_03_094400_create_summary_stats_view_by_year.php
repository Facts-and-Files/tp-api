<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS summary_stats_view_by_year');

        DB::statement('
            CREATE VIEW summary_stats_view_by_year AS
            SELECT
              t1.Year AS Year,
              0 AS Month,
              t1.ScoreTypeId AS ScoreTypeId,
              t1.UniqueUsers AS UniqueUsersPerScoreType,
              t1.UniqueItems AS UniqueItemsPerScoreType,
              t2.UniqueUsers AS OverallUniqueUsers,
              t2.UniqueItems AS OverallUniqueItems,
              COALESCE(t3.NumberOfFirstRecords, 0) AS OverallItemsStarted,
              t1.Amount AS Amount
            FROM
              (
                (
                  (
                    (
                      SELECT
                        YEAR(transcribathon.Score.Timestamp) AS Year,
                        transcribathon.Score.ScoreTypeId AS ScoreTypeId,
                        COUNT(DISTINCT transcribathon.Score.UserId) AS UniqueUsers,
                        COUNT(DISTINCT transcribathon.Score.ItemId) AS UniqueItems,
                        SUM(transcribathon.Score.Amount) AS Amount
                      FROM
                        transcribathon.Score
                      GROUP BY
                        YEAR(transcribathon.Score.Timestamp),
                        transcribathon.Score.ScoreTypeId
                    )
                  ) t1
                  JOIN (
                    SELECT
                      YEAR(transcribathon.Score.Timestamp) AS Year,
                      COUNT(DISTINCT transcribathon.Score.UserId) AS UniqueUsers,
                      COUNT(DISTINCT transcribathon.Score.ItemId) AS UniqueItems
                    FROM
                      transcribathon.Score
                    GROUP BY
                      YEAR(transcribathon.Score.Timestamp)
                  ) t2 ON(
                    (t1.Year = t2.Year)
                  )
                )
                LEFT JOIN (
                  SELECT
                    YEAR(s.Timestamp) AS Year,
                    COUNT(0) AS NumberOfFirstRecords
                  FROM
                    (
                      transcribathon.Score s
                      JOIN (
                        SELECT
                          transcribathon.Score.ItemId AS ItemId,
                          MIN(transcribathon.Score.ScoreId) AS FirstScoreId
                        FROM
                          transcribathon.Score
                        GROUP BY
                          transcribathon.Score.ItemId
                      ) firstRecords ON(
                        (
                          (s.ItemId = firstRecords.ItemId)
                          and (s.ScoreId = firstRecords.FirstScoreId)
                        )
                      )
                    )
                  GROUP BY
                    YEAR(s.Timestamp)
                ) t3 ON(
                  (t1.Year = t3.Year)
                )
              )
        ');
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS summary_stats_view_by_year');
    }
};
