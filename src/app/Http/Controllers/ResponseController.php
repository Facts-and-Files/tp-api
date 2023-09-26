<?php

namespace App\Http\Controllers;

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

    /**
     * success response method.
     *
     * @param  \Illuminate\Http\Resources\Json\JsonResource $result
     * @param  string                                       $message
     * @return \Illuminate\Http\Response
     */
    public static function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message
        ];

        return response()->json($response, 200);
    }

    /**
     * partly success response method.
     *
     * @param  \Illuminate\Http\Resources\Json\JsonResource $result
     * @param  array                                        $errors
     * @param  string                                       $message
     * @return \Illuminate\Http\Response
     */
    public static function sendPartlyResponse($result, $errors, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'error'   => $errors,
            'message' => $message
        ];

        return response()->json($response, 207);
    }

    /**
     * return error response.
     *
     * @param  string  $error
     * @param  array   $errorMessages
     * @param  integer $code
     * @return \Illuminate\Http\Response
     */
    public static function sendError($error, $errorMessages = [], $code = 404)
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

    protected function getDataByRequest(Request $request, Model $model, array $queryColumns, string $initialSortColumn): Collection
    {
        $queries = $request->query();

        // $broadMatch = $queries['broadMatch'] ?? false;
        $broadMatch =  empty($queries['broadMatch'])
            ? false
            : filter_var($queries['broadMatch'], FILTER_VALIDATE_BOOLEAN);

        $data = $model->whereRaw('1 = 1');

        foreach ($queries as $queryName => $queryValue) {
            if (array_key_exists($queryName, $queryColumns)) {
                if ($broadMatch === false) {
                    $data->where($queryColumns[$queryName], $queryValue);
                } else {
                    $data->where($queryColumns[$queryName], 'LIKE', '%' . $queryValue . '%');
                }
            }
        }

        $data = $this->filterDataByQueries($data, $queries, $initialSortColumn);

        return $data;
    }

    protected function filterDataByQueries(Builder $data, array $queries, string $initialSortColumn): Collection
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
}
