<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class ResponseController extends Controller
{
    protected $meta = [];

    public function sendResponseWithMeta(JsonResource $result, string $message): JsonResponse
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'meta'    => $this->meta,
            'message' => $message
        ];

        return response()->json($response, 200);
    }

    public static function sendResponse(JsonResource $result, string $message): JsonResponse
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message
        ];

        return response()->json($response, 200);
    }

    public static function sendPartlyResponse(
        JsonResource $result,
        array $errors,
        string $message
    ): JsonResponse
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'error'   => $errors,
            'message' => $message
        ];

        return response()->json($response, 207);
    }

    public static function sendError(
        string $error,
        string|array $errorMessages,
        int $code = 404
    ): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    protected function getDataByRequest(
        Request $request,
        Model $model,
        array $queryColumns,
        string $initialSortColumn
    ): Collection
    {
        $queries = $request->query();

        $broadMatch = empty($queries['broadMatch'])
            ? false
            : filter_var($queries['broadMatch'], FILTER_VALIDATE_BOOLEAN);

        $sep = $queries['separator'] ?? false;

        $data = $model->query();

        foreach ($queries as $queryName => $queryValue) {
            if (array_key_exists($queryName, $queryColumns)) {
                if ($sep) {
                    $queryValueArray = array_map('trim', explode($sep, $queryValue));
                    $data->whereIn($queryColumns[$queryName], $queryValueArray);
                    continue;
                }

                if ($broadMatch === false) {
                    $data->where($queryColumns[$queryName], $queryValue);
                } else {
                    $data->where($queryColumns[$queryName], 'LIKE', '%' . $queryValue . '%');
                }
            }
        }

        $data = $this->filterDataByDateTime($data, $queries);

        $data = $this->filterDataByQueries($data, $queries, $initialSortColumn);

        return $data;
    }

    protected function filterDataByDateTime(Builder $data, array $queries): Builder
    {
        $to   = isset($queries['to']) ? Carbon::parse($queries['to']) : null;
        $from = isset($queries['from']) ? Carbon::parse($queries['from']) : null;
        $timeColumn = $queries['timeColumn'] ?? 'Timestamp';

        if ($from && $to) {
            $data->whereBetween($timeColumn, [$from, $to]);
        } elseif ($from) {
            $data->where($timeColumn, '>=', $from);
        } elseif ($to) {
            $data->where($timeColumn, '<=', $to);
        }

        return $data;
    }

    protected function filterDataByQueries(
        Builder $data,
        array $queries,
        string $initialSortColumn
    ): Collection
    {
        $limit = $queries['limit'] ?? 100;
        $page = $queries['page'] ?? 1;
        $orderBy = $queries['orderBy'] ?? $initialSortColumn;
        $orderBy = $orderBy === 'id' ? $initialSortColumn : $orderBy;
        $orderDir = $queries['orderDir'] ?? 'asc';
        $offset = $limit * ($page - 1);

        $this->meta = [
            'limit' => (int) $limit,
            'currentPage' => (int) $page,
            'lastPage' => ceil($data->count() / $limit),
            'fromEntry' => ($page - 1) * $limit + 1,
            'toEntry' => min($page * $limit, $data->count()),
            'totalEntries' => $data->count()
        ];

        $filtered = $data
            ->limit($limit)
            ->offset($offset)
            ->orderBy($orderBy, $orderDir)
            ->get();

        return $filtered;
    }

    protected function filterDataByFieldlist(
        Collection $data,
        Request $request,
        array $defaults,
        string $initialSortColumn
    ): Collection
    {
        $queries = $request->query();

        if (empty($queries['fieldlist'])) {
            return $data;
        }

        $fieldlist = explode(',', $queries['fieldlist']);
        $fieldlist = array_map('trim', $fieldlist);
        $fieldlist[] = $queries['orderBy'] ?? $initialSortColumn;
        $fieldlist = array_merge($defaults, $fieldlist);
        $fieldlist = array_unique($fieldlist);
        $dataArray = $data->toArray()[0] ?: $data->toArray();
        $fieldsToRemove = array_filter(array_keys($dataArray), function($field) use ($fieldlist) {
            return !in_array($field, $fieldlist);
        });

        $data->each(function($data) use ($fieldsToRemove) {
            $data->makeHidden($fieldsToRemove);
        });

        return $data;
    }
}
