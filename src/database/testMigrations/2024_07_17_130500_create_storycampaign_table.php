<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('StoryCampaign', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->integer('StoryId');
            $table->integer('CampaignId');

            $table->foreign('CampaignId')
                  ->references('CampaignId')
                  ->on('Campaign')
                  ->restrictOnDelete()
                  ->restrictOnUpdate();
            $table->foreign('StoryId')
                  ->references('StoryId')
                  ->on('Story')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('StoryCampaign');
    }
};

