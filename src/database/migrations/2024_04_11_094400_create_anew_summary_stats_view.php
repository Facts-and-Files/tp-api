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
                AND t1.Month = t2.Month;
        ');
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS summary_stats_view');
    }
};
