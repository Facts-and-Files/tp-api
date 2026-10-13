<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ResponseController extends Controller
{
    protected $meta = [];

    public function sendResponseWithMeta(JsonResource $result, string $message): JsonResponse
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'meta'    => $this->meta,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    public static function sendResponse(
        JsonResource|array $result,
        string $message,
        int $status = 200,
    ): JsonResponse {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, $status);
    }

    public static function sendPartlyResponse(
        JsonResource $result,
        array $errors,
        string $message,
    ): JsonResponse {
        $response = [
            'success' => true,
            'data'    => $result,
            'error'   => $errors,
            'message' => $message,
        ];

        return response()->json($response, 207);
    }

    public static function sendError(
        string $error,
        string|array $errorMessages,
        int $code = 404,
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    protected function getDataByRequest(
        Request $request,
        Model|Builder $modelOrBuilder,
        array $queryColumns,
        string $initialSortColumn,
    ): Collection {
        $queries = $request->query();

        $broadMatch = empty($queries['broadMatch'])
            ? false
            : filter_var($queries['broadMatch'], FILTER_VALIDATE_BOOLEAN);

        $sep = $queries['separator'] ?? false;

        // Normalize to Eloquent Builder.
        $query = $modelOrBuilder instanceof Model
            ? $modelOrBuilder->newQuery()
            : $modelOrBuilder;

        $casts = $query->getModel()->getCasts();

        foreach ($queries as $queryName => $queryValue) {
            if (!array_key_exists($queryName, $queryColumns)) {
                continue;
            }

            $column = $queryColumns[$queryName];

            $isBoolean = in_array(
                $casts[$column] ?? null,
                ['boolean', 'bool'],
                true,
            );

            if ($sep) {
                $values = array_map('trim', explode($sep, $queryValue));

                if ($isBoolean) {
                    $values = array_map(
                        fn($value) => $this->parseBooleanFilter(
                            $queryName,
                            $value,
                        ),
                        $values,
                    );
                }

                $query->whereIn($column, $values);
                continue;
            }

            if ($isBoolean) {
                $query->where(
                    $column,
                    $this->parseBooleanFilter($queryName, $queryValue),
                );
                continue;
            }

            if ($broadMatch === false) {
                $query->where($column, $queryValue);
            } else {
                $query->where($column, 'LIKE', '%' . $queryValue . '%');
            }
        }

        $query = $this->filterDataByAreaCoordinates($query, $queries);

        $query = $this->filterDataByDateTime($query, $queries);

        $query = $this->filterDataByQueries(
            $query,
            $queries,
            $initialSortColumn,
        );

        return $query->get();
    }

    protected function parseBooleanFilter(
        string $parameter,
        mixed $value,
    ): int {
        $boolean = is_scalar($value)
            ? filter_var(
                $value,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE,
            )
            : null;

        if (
            $boolean === null
            || (is_string($value) && trim($value) === '')
        ) {
            throw ValidationException::withMessages([
                $parameter => ['The filter must be a valid boolean value.'],
            ]);
        }

        return (int) $boolean;
    }

    protected function filterDataByDateTime(Builder $query, array $queries): Builder
    {
        $to   = isset($queries['to']) ? Carbon::parse($queries['to']) : null;
        $from = isset($queries['from']) ? Carbon::parse($queries['from']) : null;
        $timeColumn = $queries['timeColumn'] ?? 'Timestamp';

        if ($from && $to) {
            $query->whereBetween($timeColumn, [$from, $to]);
        } elseif ($from) {
            $query->where($timeColumn, '>=', $from);
        } elseif ($to) {
            $query->where($timeColumn, '<=', $to);
        }

        return $query;
    }

    protected function filterDataByAreaCoordinates(Builder $query, array $queries): Builder
    {
        $areaBounds = ['latMin', 'latMax', 'lngMin', 'lngMax'];

        $queries = collect($queries);

        if (!$queries->has($areaBounds)) {
            return $query;
        }

        if ($queries->only($areaBounds)->count() !== 4) {
            return $query;
        }

        return $query->whereBetween('Latitude', [$queries['latMin'], $queries['latMax']])
                    ->whereBetween('Longitude', [$queries['lngMin'], $queries['lngMax']]);
    }

    protected function filterDataByQueries(
        Builder $query,
        array $queries,
        string $initialSortColumn,
    ): Builder {
        $limit = max(1, (int) ($queries['limit'] ?? 100));
        $page = max(1, (int) ($queries['page'] ?? 1));
        $orderBy = $queries['orderBy'] ?? $initialSortColumn;
        $orderBy = $orderBy === 'id' ? $initialSortColumn : $orderBy;
        $orderDir = $queries['orderDir'] ?? 'asc';

        $offset = $limit * ($page - 1);
        $count = (int) $query->count();
        $hasEntries = $offset < $count;

        $this->meta = [
            'limit' => $limit,
            'currentPage' => $page,
            'lastPage' => (int) ceil($count / $limit),
            'fromEntry' => $hasEntries ? $offset + 1 : 0,
            'toEntry' => $hasEntries
                ? min($offset + $limit, $count)
                : 0,
            'totalEntries' => $count,
        ];

        return $query
            ->limit($limit)
            ->offset($offset)
            ->orderBy($orderBy, $orderDir);
    }

    protected function filterDataByFieldlist(
        Collection $data,
        Request $request,
        array $defaults,
        string $initialSortColumn,
    ): Collection {
        $queries = $request->query();

        if (empty($queries['fieldlist'])) {
            return $data;
        }

        $fieldlist = explode(',', $queries['fieldlist']);
        $fieldlist = array_map('trim', $fieldlist);
        $fieldlist[] = $queries['orderBy'] ?? $initialSortColumn;
        $fieldlist = array_merge($defaults, $fieldlist);
        $fieldlist = array_unique($fieldlist);
        $dataArray = $data->toArray()[0] ?? $data->toArray();
        $fieldsToRemove = array_filter(array_keys($dataArray), function ($field) use ($fieldlist) {
            return !in_array($field, $fieldlist);
        });

        $data->each(function ($data) use ($fieldsToRemove) {
            $data->makeHidden($fieldsToRemove);
        });

        return $data;
    }
}
