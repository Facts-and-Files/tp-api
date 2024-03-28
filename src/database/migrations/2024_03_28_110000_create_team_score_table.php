<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TeamScore', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->primary(['TeamId', 'ScoreId']);
            $table->integer('TeamId');
            $table->integer('ScoreId');

            $table->index('TeamId');
            $table->index('ScoreId');

            $table->foreign('TeamId')
                  ->references('TeamId')
                  ->on('Team')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->foreign('ScoreId')
                  ->references('ScoreId')
                  ->on('Score')
                  ->cascadeOnDelete();
        });

        $this->populateTable();
    }

    protected function populateTable(): void
    {
        $teamUsers = DB::table('TeamUser')
            ->orderBy('TeamId')
            ->orderBy('UserId')
            ->get();

        $teamAssociations = [];

        foreach ($teamUsers as $teamUser) {
            $teamId = $teamUser->TeamId;
            $userId = $teamUser->UserId;

            DB::table('Score')
                ->where('UserId', $userId)
                ->orderBy('ScoreId')
                ->chunk(100, function ($scores) use ($teamId, &$teamAssociations) {
                foreach ($scores as $score) {
                    $teamAssociations[] = [
                        'TeamId' => $teamId,
                        'ScoreId' => $score->ScoreId
                    ];
                }
            });
        }

        $chunks = array_chunk($teamAssociations, 100);

        foreach ($chunks as $chunk) {
            DB::table('TeamScore')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('TeamScore');
    }
};

