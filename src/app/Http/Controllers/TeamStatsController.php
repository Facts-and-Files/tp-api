<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\Team;
use App\Models\ScoreType;
use App\Http\Resources\TeamStatsResource;
use App\Traits\Rename;

class TeamStatsController extends ResponseController
{
    use Rename;

    public function show(int $id): JsonResponse
    {
        try {
            $team = Team::findOrFail($id);

            $scoreTypes = ScoreType::get();

            $summary = [
                'Miles' => 0
            ];
            $scoreTypes->map(function ($score) use (&$summary) {
                $scoreName = $this->rename($score->Name);
                $summary[$scoreName] = 0;
            });

            $users = $team
                ->scores
                ->groupBy('UserId')
                ->map(function ($scores, $userId) use ($scoreTypes, &$summary) {
                    $userStat = [
                        'UserId' => $userId,
                        'Miles'  => 0,
                        'Items'  => 0
                    ];

                    $scoreTypes->map(function ($score) use (&$userStat){
                        $scoreName = $this->rename($score->Name);
                        $userStat[$scoreName] = 0;
                    });

                    $scores->map(function ($score) use ($scoreTypes, &$userStat) {
                        $nameAndRates = $scoreTypes->where('ScoreTypeId', $score->ScoreTypeId);
                        $name = $this->rename($nameAndRates->pluck('Name')->first());
                        $rate = $nameAndRates->pluck('Rate')->first();
                        $userStat[$name] += $score->Amount;
                        $userStat['Miles'] += $score->Amount * $rate;
                    });

                    $userStat['Miles'] = ceil($userStat['Miles']);
                    $userStat['Items'] = $scores->pluck('ItemId')->unique()->count();

                    $summary['Miles'] += $userStat['Miles'];

                    $scoreTypes->map(function ($score) use ($userStat, &$summary){
                        $scoreName = $this->rename($score->Name);
                        $summary[$scoreName] += $userStat[$scoreName];
                    });

                    return $userStat;
                });

            $summary['Items'] = $team->scores->pluck('ItemId')->unique()->count();

            $data = [
                'TeamId'  => $id,
                'Summary' => $summary,
                'Users'   => $users->values()
            ];

            $resource = new TeamStatsResource($data);

            return $this->sendResponse($resource, 'TeamStats fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }
}
