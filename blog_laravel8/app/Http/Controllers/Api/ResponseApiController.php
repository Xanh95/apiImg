<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResponseApiController extends Controller
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
