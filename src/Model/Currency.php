<?php

namespace App\Model;

class Currency
{
    private $code;
    private $rate;

    public function __construct($code, $rate)
    {
        $this->code = $code;
        $this->rate = $rate;
    }

    public function isDefault(): bool
    {
        return $this->rate == 1;
    }
}
