<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse(string $message = 'Failed', int $code = 400, $errors = null): JsonResponse{
        return response()->json([
            'status' => 'failed',
            'message' => $message,
            'data' => $errors
        ], $code);
    }
}
