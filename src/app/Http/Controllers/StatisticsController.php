<?php

namespace App\Http\Controllers;

use App\Models\SummaryStatsView;
use App\Models\SummaryStatsViewByYear;
use App\Http\Resources\StatisticsResource;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatisticsController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        try {
            $request->query->set('limit', '1000000');

            $allowedOrderBy = ['Year', 'Month', 'ScoreTypeId'];

            if (!in_array($request->query('orderBy', 'Year'), $allowedOrderBy, true)) {
                $request->query->set('orderBy', 'Year');
            }

            if (!in_array(strtolower($request->query('orderDir', 'asc')), ['asc', 'desc'], true)) {
                $request->query->set('orderDir', 'asc');
            }

            $data = $this->getCachedStatistics($request);

            return $this->sendResponse(
                new StatisticsResource($data),
                'Statistics fetched.',
            );
        } catch (\Exception $exception) {
            return $this->sendError(
                'Invalid data',
                $exception->getMessage(),
                400,
            );
        }
    }

    private function getCachedStatistics(Request $request): Collection
    {
        $queryColumns = [
            'Year' => 'Year',
            'Month' => 'Month',
            'ScoreTypeId' => 'ScoreTypeId',
        ];

        $initialSortColumn = 'Year';

        $forceFresh = $request->boolean('fresh', false);

        $now = CarbonImmutable::now();

        $cacheSuffix = $this->statisticsCacheSuffix($request);

        $historyMonths = $this->rememberStatistics(
            "statistics:v1:history:months:{$cacheSuffix}",
            forever: true,
            forceFresh: $forceFresh,
            callback: fn() => $this->getDataByRequest(
                $request,
                $this->historyMonthsQuery($now),
                $queryColumns,
                $initialSortColumn,
            ),
        );

        $historyYears = $this->rememberStatistics(
            "statistics:v1:history:years:{$cacheSuffix}",
            forever: true,
            forceFresh: $forceFresh,
            callback: fn() => $this->getDataByRequest(
                $request,
                $this->historyYearsQuery($now),
                $queryColumns,
                $initialSortColumn,
            ),
        );

        $currentMonth = $this->rememberStatistics(
            "statistics:v1:current:month:{$now->format('Y-m')}:{$cacheSuffix}",
            forever: false,
            forceFresh: $forceFresh,
            callback: fn() => $this->getDataByRequest(
                $request,
                $this->currentMonthQuery($now),
                $queryColumns,
                $initialSortColumn,
            ),
        );

        $currentYear = $this->rememberStatistics(
            "statistics:v1:current:year:{$now->year}:{$cacheSuffix}",
            forever: false,
            forceFresh: $forceFresh,
            callback: fn() => $this->getDataByRequest(
                $request,
                $this->currentYearQuery($now),
                $queryColumns,
                $initialSortColumn,
            ),
        );

        return $historyMonths
            ->concat($historyYears)
            ->concat($currentMonth)
            ->concat($currentYear)
            ->values();
    }

    private function historyMonthsQuery(CarbonImmutable $now): EloquentBuilder
    {
        return SummaryStatsView::query()
            ->where(function (EloquentBuilder $query) use ($now) {
                $query->where('Year', '<', $now->year)
                    ->orWhere(function (EloquentBuilder $query) use ($now) {
                        $query->where('Year', $now->year)
                            ->where('Month', '<', $now->month);
                    });
            });
    }

    private function currentMonthQuery(CarbonImmutable $now): EloquentBuilder
    {
        return SummaryStatsView::query()
            ->where('Year', $now->year)
            ->where('Month', $now->month);
    }

    private function historyYearsQuery(CarbonImmutable $now): EloquentBuilder
    {
        return SummaryStatsViewByYear::query()
            ->where('Year', '<', $now->year);
    }

    private function currentYearQuery(CarbonImmutable $now): EloquentBuilder
    {
        return SummaryStatsViewByYear::query()
            ->where('Year', $now->year);
    }

    private function statisticsCacheSuffix(Request $request): string
    {
        $filters = $request->only([
            'Year',
            'Month',
            'ScoreTypeId',
            'orderBy',
            'orderDir',
        ]);

        ksort($filters);

        return hash(
            'sha256',
            json_encode($filters, JSON_THROW_ON_ERROR),
        );
    }

    private function rememberStatistics(
        string $key,
        bool $forever,
        bool $forceFresh,
        \Closure $callback,
    ): mixed {
        if ($forceFresh) {
            Cache::forget($key);
        }

        if ($forever) {
            return Cache::rememberForever($key, $callback);
        }

        return Cache::remember(
            $key,
            now()->addDay(),
            $callback,
        );
    }

    public function alltimeIndex(): JsonResponse
    {
        try {
            $items   = DB::table('Item')->select('CompletionStatusId, TranscriptionStatusId');
            $stories = DB::table('Story')->select('CompletionStatusId');
            $scores  = DB::table('Score')->select('ScoreTypeId', 'UserId', 'Amount');

            $data = [
                'ActiveUsers'              => $this->countDistinctUsers($scores),
                'TranscriptionsNotStarted' => $this->countByCompletionStatusId($items, 1, 'TranscriptionStatusId'),
                'TranscriptionsEdited'     => $this->countByCompletionStatusId($items, 2, 'TranscriptionStatusId'),
                'TranscriptionsReviewed'   => $this->countByCompletionStatusId($items, 3, 'TranscriptionStatusId'),
                'TranscriptionsCompleted'  => $this->countByCompletionStatusId($items, 4, 'TranscriptionStatusId'),
                'ItemsNotStarted'          => $this->countByCompletionStatusId($items, 1),
                'ItemsEdited'              => $this->countByCompletionStatusId($items, 2),
                'ItemsReviewed'            => $this->countByCompletionStatusId($items, 3),
                'ItemsCompleted'           => $this->countByCompletionStatusId($items, 4),
                'StoriesNotStarted'        => $this->countByCompletionStatusId($stories, 1),
                'StoriesEdited'            => $this->countByCompletionStatusId($stories, 2),
                'StoriesReviewed'          => $this->countByCompletionStatusId($stories, 3),
                'StoriesCompleted'         => $this->countByCompletionStatusId($stories, 4),
                'ManualTranscriptions'     => $this->sumByScoreTypeId($scores, 2),
                'HTRTranscriptions'        => $this->sumByScoreTypeId($scores, 5),
                'Locations'                => $this->sumByScoreTypeId($scores, 1),
                'Enrichments'              => $this->sumByScoreTypeId($scores, 3),
                'Descriptions'             => $this->sumByScoreTypeId($scores, 4),
            ];

            $resource = new StatisticsResource($data);

            return $this->sendResponse($resource, 'Statistics fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    private function sumByScoreTypeId(Builder $query, int $scoreTypeId): int
    {
        $cloned = clone $query;
        return intval($cloned->where('ScoreTypeId', '=', $scoreTypeId)->sum('Amount'));
    }

    private function countByCompletionStatusId(Builder $query, int $completionStatusId, string $completionStatus = 'CompletionStatusId'): int
    {
        $cloned = clone $query;
        return $cloned->where($completionStatus, '=', $completionStatusId)->count();
    }

    private function countDistinctUsers(Builder $query): int
    {
        $cloned = clone $query;
        return $cloned->distinct()->count('UserId');
    }
}
