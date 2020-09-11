<?php
declare(strict_types=1);

use App\Command;
use App\Model\Currency;
use PHPUnit\Framework\TestCase;

final class CurrencyTest extends TestCase
{
    public function testErrorOnInvalidCurrencyCode1()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid currency code: BG');

        new Currency('BG', 0.12);
    }

    public function testErrorOnInvalidCurrencyCode2()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid currency code: BGNA');

        new Currency('BGNA', 0.12);
    }

    public function testErrorOnInvalidCurrencyRate2()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid currency rate: -0.01');

        new Currency('BGN', -0.01);
    }

    public function testIsDefault()
    {
        $this->assertTrue((new Currency('BGN', 1))->isDefault());
    }

    public function testIsNotDefault()
    {
        $this->assertFalse((new Currency('BGN', 1.001))->isDefault());
    }
}
