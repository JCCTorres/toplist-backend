<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test()
    {
        return response()->json([
            'message' => 'API funcionando',
            'timestamp' => now(),
            'method' => 'GET'
        ]);
    }

    public function testPost(Request $request)
    {
        return response()->json([
            'message' => 'POST funcionando',
            'timestamp' => now(),
            'data' => $request->all()
        ]);
    }
}