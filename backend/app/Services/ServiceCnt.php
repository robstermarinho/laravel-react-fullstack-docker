<?php

namespace App\Services;

class ServiceCnt
{
    protected $cnt;

    public function __construct()
    {
        $this->cnt = 0;
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
