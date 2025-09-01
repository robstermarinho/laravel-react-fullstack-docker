<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ServiceCnt
{
    protected $cnt;

    public function __construct()
    {
        $this->cnt = 0;
        Log::info('ServiceCnt constructor ');
    }

    public function increment()
    {
        $this->cnt = $this->cnt + 100;
    }

    public function getCnt()
    {
        return $this->cnt;
    }
}
