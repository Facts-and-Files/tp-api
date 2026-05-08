<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS item_status_update');
        DB::unprepared('DROP TRIGGER IF EXISTS story_status_update');
    }

    public function down(): void
    {
        DB::unprepared("
			CREATE TRIGGER item_status_update
			BEFORE UPDATE ON Item FOR EACH ROW
			BEGIN
				IF (NEW.TranscriptionStatusId != 4)
					THEN
						SET NEW.CompletionStatusId = NEW.TranscriptionStatusId;
				ELSEIF
					(NEW.TranscriptionStatusId = 4 AND
					NEW.DescriptionStatusId = 4 AND
					NEW.LocationStatusId = 4 AND
					NEW.TaggingStatusId = 4)
					THEN
						IF (NEW.CompletionStatusId != 4)
							THEN
								SET NEW.CompletionStatusId = 4;
						END IF;
				ELSE
						SET NEW.CompletionStatusId = 3;
				END IF;
			END
		");

        DB::unprepared("
			CREATE TRIGGER story_status_update
			AFTER UPDATE ON items FOR EACH ROW
            BEGIN
	            IF (NEW.CompletionStatusId != OLD.CompletionStatusId) THEN
		            IF ((SELECT count(*) FROM Item WHERE StoryId = NEW.StoryId AND CompletionStatusId != 4) = 0) THEN
			            UPDATE Story
			            SET CompletionStatusId = 4
			            WHERE StoryId = NEW.StoryId;
		            ELSEIF (NEW.CompletionStatusId = 3 AND (SELECT CompletionStatusId FROM Story WHERE StoryId = NEW.StoryId) < 3) THEN
			            UPDATE Story
			            SET CompletionStatusId = 3
			            WHERE StoryId = NEW.StoryId;
		            ELSEIF (NEW.CompletionStatusId = 2 AND (SELECT CompletionStatusId FROM Story WHERE StoryId = NEW.StoryId) < 2) THEN
			            UPDATE Story
			            SET CompletionStatusId = 2
			            WHERE StoryId = NEW.StoryId;
		            END IF;
	            END IF;
            END
		");
    }
};
