<?php


namespace App\Model;

use RuntimeException;

class Total
{
    private $customerName;
    private $currencyCode;
    private $total;

    public function __construct(string $customerName, string $currencyCode)
    {
        $this->customerName = $customerName;
        $this->currencyCode = $currencyCode;
        $this->total = 0;
    }

    public function subtract(float $value): Total
    {
        if ($value > $this->total) {
            throw new RuntimeException("Invoice cannot have negative value!");
        }

        $this->total = $this->total - $value;

        return $this;
    }

    public function add(float $value): Total
    {
        $this->total = $this->total + $value;

        return $this;
    }

    public function getTotal(): float
    {
        return round($this->total, 2);
    }

    public function __toString()
    {
        return "{$this->customerName} - {$this->getTotal()} {$this->currencyCode}";
    }
}
