<?php

namespace App\Model;

use InvalidArgumentException;

class Invoice
{
    const INVOICE = 1;
    const CREDIT_NOTE = 2;
    const DEBIT_NOTE = 3;

    /**
     * @var string
     */
    private $customer;

    /**
     * @var string
     */
    private $vatNumber;

    /**
     * @var string
     */
    private $number;

    /**
     * @var int
     */
    private $type;

    /**
     * @var string
     */
    private $parent;

    /**
     * @var string
     */
    private $currencyCode;

    /**
     * @var float
     */
    private $total;

    public function __construct(array $data)
    {
        if (count($data) != 7 || !$this->isValidType((int) $data[3])) {
            throw new InvalidArgumentException('Invalid invoice type: ' . implode(',', $data));
        }


        $this->customer = $data[0];
        $this->vatNumber = $data[1];
        $this->number = $data[2];
        $this->type = (int) $data[3];
        $this->parent = $data[4];
        $this->currencyCode = $data[5];
        $this->total = (float) $data[6];
    }

    private function isValidType(int $type): bool
    {
        return in_array($type, [self::INVOICE, self::CREDIT_NOTE,self::DEBIT_NOTE]);
    }

    public function isCredit()
    {
        return $this->getType() == self::CREDIT_NOTE;
    }

    public function getCustomer(): string
    {
        return $this->customer;
    }

    public function getVatNumber(): string
    {
        return $this->vatNumber;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getParent(): string
    {
        return $this->parent;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function getTotal(): float
    {
        return $this->total;
    }
}
