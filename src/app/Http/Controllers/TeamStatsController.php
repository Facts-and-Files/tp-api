<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\Team;
use App\Models\ScoreType;
use App\Http\Resources\TeamStatsResource;

class TeamStatsController extends ResponseController
{
    public function show(int $id): JsonResponse
    {
        try {
            $team = Team::findOrFail($id);

            $scoreTypes = ScoreType::get();

            $summary = [];
            $summary['Miles'] = 0;
            $scoreTypes->map(function ($score) use (&$summary) {
                $summary[$score->Name] = 0;
            });

            $users = $team
                ->scores
                ->groupBy('UserId')
                ->map(function ($scores, $userId) use ($scoreTypes, &$summary) {
                    $userStat = [];
                    $userStat['UserId'] = $userId;
                    $userStat['Miles'] = 0;

                    $scoreTypes->map(function ($score) use (&$userStat){
                        $userStat[$score->Name] = 0;
                    });

                    $scores->map(function ($score) use ($scoreTypes, &$userStat) {
                        $nameAndRates = $scoreTypes->where('ScoreTypeId', $score->ScoreTypeId);
                        $name = $nameAndRates->pluck('Name')->first();
                        $rate = $nameAndRates->pluck('Rate')->first();
                        $userStat[$name] += $score->Amount;
                        $userStat['Miles'] += $score->Amount * $rate;
                    });

                    $userStat['Miles'] = ceil($userStat['Miles']);

                    $summary['Miles'] += $userStat['Miles'];

                    $scoreTypes->map(function ($score) use ($userStat, &$summary){
                        $summary[$score->Name] += $userStat[$score->Name];
                    });

                    return $userStat;
                });

            $data = [
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
