<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResponseApi extends Controller
{
    //
    public function handleSuccess($data, $message): JsonResponse
    {
        $response = [
            "success" => true,
            "data" => $data,
            "message" => $message
        ];
        return response()->json($response);
    }
    public function handleError($message, $code)
    {
        $response = [
            'message' => $message
        ];
        return response()->json($response, $code);
    }
}
