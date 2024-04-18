<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS summary_stats_view');

        DB::statement('
            CREATE VIEW summary_stats_view AS
            SELECT
                t1.Year,
                t1.Month,
                t1.ScoreTypeId,
                t1.UniqueUsers AS UniqueUsersPerScoreType,
                t1.UniqueItems AS UniqueItemsPerScoreType,
                t2.UniqueUsers AS OverallUniqueUsers,
                t2.UniqueItems AS OverallUniqueItems,
                COALESCE(t3.NumberOfFirstRecords, 0) AS OverallItemsStarted,
                t1.Amount
            FROM
                (
                    SELECT
                        YEAR(Timestamp) AS Year,
                        MONTH(Timestamp) AS Month,
                        ScoreTypeId,
                        COUNT(DISTINCT UserId) AS UniqueUsers,
                        COUNT(DISTINCT ItemId) AS UniqueItems,
                        SUM(Amount) AS Amount
                    FROM
                        Score
                    GROUP BY
                        YEAR(Timestamp),
                        MONTH(Timestamp),
                        ScoreTypeId
                ) AS t1
            JOIN
                (
                    SELECT
                        YEAR(Timestamp) AS Year,
                        MONTH(Timestamp) AS Month,
                        COUNT(DISTINCT UserId) AS UniqueUsers,
                        COUNT(DISTINCT ItemId) AS UniqueItems
                    FROM
                        Score
                    GROUP BY
                        YEAR(Timestamp),
                        MONTH(Timestamp)
                ) AS t2
            ON
                t1.Year = t2.Year
                AND t1.Month = t2.Month
            LEFT JOIN
                (
                    SELECT
                        YEAR(s.Timestamp) AS Year,
                        MONTH(s.Timestamp) AS Month,
                        COUNT(*) AS NumberOfFirstRecords
                    FROM
                        Score s
                    INNER JOIN (
                        SELECT
                            ItemId,
                            MIN(ScoreId) AS FirstScoreId
                        FROM
                            Score
                        GROUP BY
                            ItemId
                    ) AS firstRecords
                    ON
                        s.ItemId = firstRecords.ItemId
                        AND s.ScoreId = firstRecords.FirstScoreId
                    GROUP BY
                        YEAR(s.Timestamp), MONTH(s.Timestamp)
                ) AS t3
            ON
                t1.Year = t3.Year
                AND t1.Month = t3.Month;
        ');
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS summary_stats_view');
    }
};
