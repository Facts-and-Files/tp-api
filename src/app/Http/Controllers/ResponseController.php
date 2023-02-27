<?php

namespace App\Http\Controllers;

class ResponseController extends Controller
{
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
}
