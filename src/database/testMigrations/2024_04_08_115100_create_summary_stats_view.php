<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE VIEW summary_stats_view AS
            SELECT
                strftime('%Y', Timestamp) AS Year,
                strftime('%m', Timestamp) AS Month,
                ScoreTypeId,
                COUNT(DISTINCT UserId) AS UniqueUsersPerScoreType,
                COUNT(DISTINCT ItemId) AS UniqueItemsPerScoreType,
                t2.UniqueUsers AS OverallUniqueUsers,
                t2.UniqueItems AS OverallUniqueItems,
                SUM(Amount) AS Amount
            FROM
                Score
            JOIN
                (
                    SELECT
                        strftime('%Y', Timestamp) AS Year,
                        strftime('%m', Timestamp) AS Month,
                        COUNT(DISTINCT UserId) AS UniqueUsers,
                        COUNT(DISTINCT UserId) AS UniqueItems
                    FROM
                        Score
                    GROUP BY
                        strftime('%Y', Timestamp),
                        strftime('%m', Timestamp)
                ) AS t2
            ON
                strftime('%Y', Score.Timestamp) = t2.Year
                AND strftime('%m', Score.Timestamp) = t2.Month
            GROUP BY
                Year, Month, ScoreTypeId;
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS summary_stats_view');
    }
};
