<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ApiJob implements ShouldQueue
{
    use Queueable;

    private string $taskId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $taskId = '')
    {
        $this->taskId = strlen($taskId) > 0 ? $taskId : uniqid('api_job');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("ApiJob started", ['task_id' => $this->taskId]);


        for ($i = 1; $i <= 10; $i++) {
            sleep(1);
            Log::info("ApiJob progresss", [
                'task_id' => $this->taskId,
                'seconds_elapsed' => $i,
                'progress' => ($i / 10) * 100 . '%'
            ]);
        }
    }
}
