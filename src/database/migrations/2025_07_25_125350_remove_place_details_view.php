<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS place_combined_details_view');
    }

    public function down(): void
    {
        DB::statement('
            CREATE VIEW place_combined_details_view AS
            SELECT
                p.PlaceId,
                p.ItemId,
                p.Name,
                p.Latitude,
                p.Longitude,
                p.WikidataId,
                p.WikiDataName,
                p.Link,
                p.Comment,
                p.UserGenerated,
                p.UserId,
                p.PlaceRole,
                i.Title as ItemTitle,
                i.StoryId,
                `s`.`dc:title` as StoryTitle,
                s.ProjectId
            FROM Place p
            LEFT JOIN Item i ON p.ItemId = i.ItemId
            LEFT JOIN Story s ON i.StoryId = s.StoryId

            UNION ALL

            SELECT
                NULL as PlaceId,
                NULL as ItemId,
                s.PlaceName as Name,
                s.PlaceLatitude as Latitude,
                s.PlaceLongitude as Longitude,
                s.PlaceLink as WikidataId,
                s.PlaceComment as WikiDataName,
                NULL as Link,
                NULL as Comment,
                s.PlaceUserGenerated as UserGenerated,
                s.PlaceUserId as UserId,
                "StoryPlace" as PlaceRole,
                NULL as ItemTitle,
                s.StoryId,
                `s`.`dc:title` as StoryTitle,
                s.ProjectId
            FROM Story s
            WHERE s.PlaceName IS NOT NULL
        ');
    }
};
