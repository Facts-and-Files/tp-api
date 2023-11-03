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
        $apiV1Connection = $this->checkApiV1Connection();

        $data['ScriptExecutionTimeMS'] = $executionTimeMS;
        $data['ScriptExecutionTimeStatus'] = $executionTimeMS <= 200
            ? 'Ok'
            : ($executionTimeMS <= 500 ? 'Moderate' : 'Slow');

        $data['DatabaseConnectionStatus'] = $databaseConnection['conected'];
        $data['DatabaseConnectionTimeMS'] = $databaseConnection['time'];

        $data['DatabaseExecutionTimeMS'] = $databaseExecutionMS;
        $data['DatabaseExecutionTimeStatus'] = $databaseExecutionMS <= 10
            ? 'Ok'
            : ($databaseExecutionMS <= 50 ? 'Moderate' : 'Slow');

        $data['NetworkConnectionStatus'] = $networkConnection['conected'];
        $data['NetworkConnectionTimeMS'] = $networkConnection['time'];
        $data['NetworkConnectionTimeStatus'] = $networkConnection['time'] <= 100
            ? 'Ok'
            : ($networkConnection['time'] <= 1000 ? 'Moderate' : 'Slow');

        $data['APIv1ConnectionStatus'] = $apiV1Connection['conected'];
        $data['APIv1ConnectionTimeMS'] = $apiV1Connection['time'];
        $data['APIv1ConnectionTimeStatus'] = $apiV1Connection['time'] <= 200
            ? 'Ok'
            : ($apiV1Connection['time'] <= 500 ? 'Moderate' : 'Slow');

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

    protected function checkApiV1Connection(): array
    {
        $data = [];
        $data['conected'] = 'Failed';
        $data['time'] = 0;

        try {
            $start = microtime(true);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,'http://transcribathon.eu/tp-api/projects/');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $response = curl_exec($ch);
            $end = microtime(true);
            if ($response) {
                curl_close($ch);
                $data['conected'] = 'Ok';
                $data['time'] = round(($end - $start) * 1000);
            }
        } catch(Exception $exception) { }

        return $data;
    }
}
