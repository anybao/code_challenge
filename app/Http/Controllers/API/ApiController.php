<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    /**
     * Return generic json response with the given data.
     *
     * @param $data
     * @param int $statusCode
     * @param array $headers
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respond($data, $statusCode = 200, $message = '', $headers = [])
    {
        $newData = [];

        if (!isset($data['data'])) {
            $newData['data'] = $data;
        } else {
            $newData = $data;
        }

        $newData['success'] = [
            'message' => $message
        ];

        return response()->json($newData, $statusCode, $headers);
    }
}
