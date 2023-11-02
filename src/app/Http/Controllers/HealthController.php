<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ResponseController;
use App\Http\Resources\HealthResource;

class HealthController extends ResponseController
{
    public function check(Request $request): JsonResponse
    {
        $data = [];

        $executionTimeMS = $this->checkExecutionTime();
        $databaseConnection = $this->checkDatabaseConnection();
        $databaseExecutionMS = $this->checkDatabaseExecutionTime();
        $networkConnection = $this->checkNetworkConnection();

        $data['ScriptExecutionTimeMS'] = $executionTimeMS;
        $data['ScriptExecutionTimeStatus'] = $executionTimeMS <= 100
            ? 'Ok'
            : ($executionTimeMS <= 500 ? 'Slow' : 'Very Slow');

        $data['DatabaseConnectionStatus'] = $databaseConnection['conected'];
        $data['DatabaseConnectionTimeMS'] = $databaseConnection['time'];

        $data['DatabaseExecutionTimeMS'] = $databaseExecutionMS;
        $data['DatabaseExecutionTimeStatus'] = $databaseExecutionMS <= 1
            ? 'Ok'
            : ($databaseExecutionMS <= 10 ? 'Slow' : 'Very Slow');

        $data['NetworkConnectionStatus'] = $networkConnection['conected'];
        $data['NetworkConnectionTimeMS'] = $networkConnection['time'];
        $data['NetworkConnectionTimeStatus'] = $networkConnection['time'] <= 100
            ? 'Ok'
            : ($networkConnection['time'] <= 1000 ? 'Slow' : 'Very Slow');

        $resource = new HealthResource($data);

        return $this->sendResponse($resource, 'Health data fetched.');
    }

    protected function checkExecutionTime(): int
    {
        return round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000);
    }

    protected function checkDatabaseConnection(): array
    {
        $data = [];

        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $end = microtime(true);

            $data['conected'] = 'Ok' ;
            $data['time'] = round(($end - $start) * 1000);
        } catch (\Exception $exception) {
            $data['conected'] = 'Failed';
            $data['time'] = 0;
        }

        return $data;
    }

    protected function checkDatabaseExecutionTime(): int
    {
        try {
            $start = microtime(true);
            $result = DB::select('SHOW TABLES');
            $end = microtime(true);

            return round(($end - $start) * 1000);
        } catch (\Exception $exception) {
            return 0;
        }
    }

    protected function checkNetworkConnection(): array
    {
        $data = [];
        $data['conected'] = 'Failed';
        $data['time'] = 0;

        try {
            $start = microtime(true);
            $fp = @fsockopen('1.1.1.1', '443', $errno, $errstr, 30);
            $end = microtime(true);
            if ($fp) {
                fclose($fp);
                $data['conected'] = 'Ok';
                $data['time'] = round(($end - $start) * 1000);
            }
        } catch(Exception $exception) { }

        return $data;
    }
}
