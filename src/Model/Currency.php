<?php

namespace App\Model;

use InvalidArgumentException;

class Currency
{
    private $code;
    private $rate;

    public function __construct(string $code, float $rate)
    {
        if (strlen($code) != 3) {
            throw new InvalidArgumentException("Invalid currency code: {$code}");
        }

        if ($rate <= 0) {
            throw new InvalidArgumentException("Invalid currency rate: {$rate}");
        }

        $this->code = $code;
        $this->rate = $rate;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getRate(): float
    {
        return $this->rate;
    }

    public function isDefault(): bool
    {
        return $this->getRate() == 1;
    }
}
