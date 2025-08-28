<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ApiJob;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function health()
    {
        return response()->json(['ok' => true, 'time' => now()]);
    }

    public function dispatchTestJob(Request $request)
    {
        $taskId = uniqid('api_job');

        ApiJob::dispatch($taskId);

        return response()->json([
            'message' => 'API job dispatched successfully',
            'task_id' => $taskId
        ]);
    }
}
