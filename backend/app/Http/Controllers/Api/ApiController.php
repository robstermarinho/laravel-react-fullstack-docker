<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function health()
    {
        return response()->json(['ok' => true, 'time' => now()]);
    }
}
