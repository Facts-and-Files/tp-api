<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
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
                        strftime('%Y', Score.Timestamp) AS Year,
                        Score.ScoreTypeId AS ScoreTypeId,
                        COUNT(DISTINCT Score.UserId) AS UniqueUsers,
                        COUNT(DISTINCT Score.ItemId) AS UniqueItems,
                        SUM(Score.Amount) AS Amount
                      FROM
                        Score
                      GROUP BY
                        strftime('%Y', Score.Timestamp),
                        Score.ScoreTypeId
                    )
                  ) t1
                  JOIN (
                    SELECT
                      strftime('%Y', Score.Timestamp) AS Year,
                      COUNT(DISTINCT Score.UserId) AS UniqueUsers,
                      COUNT(DISTINCT Score.ItemId) AS UniqueItems
                    FROM
                      Score
                    GROUP BY
                      strftime('%Y', Score.Timestamp)
                  ) t2 ON(
                    (t1.Year = t2.Year)
                  )
                )
                LEFT JOIN (
                  SELECT
                    strftime('%Y', s.Timestamp) AS Year,
                    COUNT(0) AS NumberOfFirstRecords
                  FROM
                    (
                      Score s
                      JOIN (
                        SELECT
                          Score.ItemId AS ItemId,
                          MIN(Score.ScoreId) AS FirstScoreId
                        FROM
                          Score
                        GROUP BY
                          Score.ItemId
                      ) firstRecords ON(
                        (
                          (s.ItemId = firstRecords.ItemId)
                          and (s.ScoreId = firstRecords.FirstScoreId)
                        )
                      )
                    )
                  GROUP BY
                    strftime('%Y', s.Timestamp)
                ) t3 ON(
                  (t1.Year = t3.Year)
                )
              );
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS summary_stats_view_by_year');
    }
};
