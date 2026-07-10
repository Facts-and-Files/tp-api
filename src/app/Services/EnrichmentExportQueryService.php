<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EnrichmentExportQueryService
{
    private const ALLOWED_FILTERS = [
        'AnnotationId',
        'EuropeanaAnnotationId',
        'ItemId',
        'LanguageCode',
        'Motivation',
        'StoryId',
        'Text',
        'TextNoTags',
        'TranscribathonItemId',
        'TranscribathonStoryId',
    ];

    public function get(Request $request): Collection
    {
        $baseQuery = DB::query()->fromSub($this->buildUnionQuery($request), 'enrichments');

        $this->applyRequestFilters($baseQuery, $request);
        $this->applySorting($baseQuery, $request);
        $this->applyPagination($baseQuery, $request);

        return $baseQuery->get()->map(function ($row) {
            $entry = (array) $row;
            $entry['Languages'] = $this->mapLanguages(
                $entry['LanguageCode'] ?? null,
                $entry['LanguageName'] ?? null,
            );

            return $entry;
        });
    }

    private function buildUnionQuery(Request $request): Builder
    {
        $annotationQuery = DB::table('Story as s')
            ->selectRaw('a.AnnotationId AS AnnotationId')
            ->selectRaw('a.Text AS Text')
            ->selectRaw('a.TextNoTags AS TextNoTags')
            ->selectRaw('a.Timestamp AS Timestamp')
            ->selectRaw('a.X_Coord AS X_Coord')
            ->selectRaw('a.Y_Coord AS Y_Coord')
            ->selectRaw('a.Width AS Width')
            ->selectRaw('a.Height AS Height')
            ->selectRaw('a.EuropeanaAnnotationId AS EuropeanaAnnotationId')
            ->selectRaw('m.Name AS Motivation')
            ->selectRaw('i.ProjectItemId AS ItemId')
            ->selectRaw('i.OrderIndex AS OrderIndex')
            ->selectRaw('i.ItemId AS TranscribathonItemId')
            ->selectRaw("i.`edm:WebResource` AS ImageLink")
            ->selectRaw('s.StoryId AS TranscribathonStoryId')
            ->selectRaw("s.`edm:landingPage` AS StoryUrl")
            ->selectRaw("s.RecordId || IFNULL(i.EuropeanaAttachment, '') AS StoryId")
            ->selectRaw('NULL AS LanguageCode')
            ->selectRaw('NULL AS LanguageName')
            ->leftJoinSub($this->buildEligibleItemsQuery($request), 'i', 's.StoryId', '=', 'i.StoryId')
            ->leftJoin('Annotation as a', 'i.ItemId', '=', 'a.ItemId')
            ->leftJoin('AnnotationType as at', 'a.AnnotationTypeId', '=', 'at.AnnotationTypeId')
            ->leftJoin('Motivation as m', 'at.MotivationId', '=', 'm.MotivationId')
            ->whereNotNull('a.AnnotationId');

        if ($request->filled('storyId')) {
            $annotationQuery->where('s.RecordId', $request->string('storyId')->toString());
        }

        $languageAggregate = DB::table('Transcription as t')
            ->selectRaw('t.TranscriptionId AS TranscriptionId')
            ->selectRaw('GROUP_CONCAT(l.Code) AS LanguageCode')
            ->selectRaw('GROUP_CONCAT(l.Name) AS LanguageName')
            ->join('TranscriptionLanguage as tl', 't.TranscriptionId', '=', 'tl.TranscriptionId')
            ->join('Language as l', 'tl.LanguageId', '=', 'l.LanguageId')
            ->groupBy('t.TranscriptionId');

        $transcriptionQuery = DB::table('Story as s')
            ->selectRaw('t.TranscriptionId AS AnnotationId')
            ->selectRaw('t.Text AS Text')
            ->selectRaw('t.TextNoTags AS TextNoTags')
            ->selectRaw('t.Timestamp AS Timestamp')
            ->selectRaw('0 AS X_Coord')
            ->selectRaw('0 AS Y_Coord')
            ->selectRaw('0 AS Width')
            ->selectRaw('0 AS Height')
            ->selectRaw('t.EuropeanaAnnotationId AS EuropeanaAnnotationId')
            ->selectRaw("'transcribing' AS Motivation")
            ->selectRaw('i.ProjectItemId AS ItemId')
            ->selectRaw('i.OrderIndex AS OrderIndex')
            ->selectRaw('i.ItemId AS TranscribathonItemId')
            ->selectRaw("i.`edm:WebResource` AS ImageLink")
            ->selectRaw('s.StoryId AS TranscribathonStoryId')
            ->selectRaw("s.`edm:landingPage` AS StoryUrl")
            ->selectRaw("s.RecordId || IFNULL(i.EuropeanaAttachment, '') AS StoryId")
            ->selectRaw('lang.LanguageCode AS LanguageCode')
            ->selectRaw('lang.LanguageName AS LanguageName')
            ->leftJoinSub($this->buildEligibleItemsQuery($request), 'i', 's.StoryId', '=', 'i.StoryId')
            ->leftJoin('Transcription as t', 'i.ItemId', '=', 't.ItemId')
            ->leftJoinSub($languageAggregate, 'lang', 'lang.TranscriptionId', '=', 't.TranscriptionId')
            ->where('t.CurrentVersion', 1)
            ->where('t.NoText', 0)
            ->whereNotNull('t.TranscriptionId');

        if ($request->filled('storyId')) {
            $storyId = $request->string('storyId')->toString();
            $transcriptionQuery->where('s.RecordId', 'like', $storyId . '%');
        }

        return $annotationQuery->union($transcriptionQuery);
    }

    private function buildEligibleItemsQuery(Request $request): Builder
    {
        $query = DB::table('Item')->select('*');

        if ($request->input('includeExported') !== '1') {
            $query->where('Exported', 0);
        }

        return $query->where('TranscriptionStatusId', 4);
    }

    private function applyRequestFilters(Builder $query, Request $request): void
    {
        foreach ($request->query() as $key => $value) {
            if (in_array($key, ['includeExported', 'storyId', 'limit', 'page', 'orderBy', 'orderDir'], true)) {
                continue;
            }

            if (!in_array($key, self::ALLOWED_FILTERS, true)) {
                throw new InvalidArgumentException('Filter [' . $key . '] is not allowed.');
            }

            $values = array_filter(array_map('trim', explode(',', (string) $value)), static fn($item) => $item !== '');

            if ($values === []) {
                continue;
            }

            $query->where(function (Builder $builder) use ($key, $values): void {
                foreach ($values as $filterValue) {
                    $builder->orWhere($key, $filterValue);
                }
            });
        }
    }

    private function applySorting(Builder $query, Request $request): void
    {
        $allowedSorts = [
            'AnnotationId',
            'EuropeanaAnnotationId',
            'ItemId',
            'OrderIndex',
            'Timestamp',
            'TranscribathonItemId',
            'TranscribathonStoryId',
        ];

        $orderBy = $request->input('orderBy', 'AnnotationId');
        $orderDir = strtolower((string) $request->input('orderDir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (!in_array($orderBy, $allowedSorts, true)) {
            $orderBy = 'AnnotationId';
        }

        $query->orderBy($orderBy, $orderDir);
    }

    private function applyPagination(Builder $query, Request $request): void
    {
        $limit = max(1, (int) $request->input('limit', 100));
        $page = max(1, (int) $request->input('page', 1));
        $offset = ($page - 1) * $limit;

        $query->limit($limit)->offset($offset);
    }

    private function mapLanguages(?string $codes, ?string $names): array
    {
        if ($codes === null || $names === null) {
            return [];
        }

        $codeList = array_map('trim', explode(',', $codes));
        $nameList = array_map('trim', explode(',', $names));
        $languages = [];

        foreach ($nameList as $index => $name) {
            if ($name === '') {
                continue;
            }

            $languages[] = [
                'Name' => $name,
                'Code' => $codeList[$index] ?? null,
            ];
        }

        return $languages;
    }
}
