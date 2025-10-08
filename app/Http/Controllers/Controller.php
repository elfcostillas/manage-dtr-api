<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function jsonResponse($data,$message,$status)
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'status' => $status,
            'count' => (is_null($data)) ? 0 : count($data)
        ]);
    }

    public function json($data,$message,$status)
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'status' => $status,
            
        ]);
    }
}
