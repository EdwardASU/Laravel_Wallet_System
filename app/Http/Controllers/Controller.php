<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //
    public function success($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
        ], $code);
    }

    public function error($message, $code = 400)
    {
        return response()->json([
            'message' => $message,
        ], $code);
    }
}
