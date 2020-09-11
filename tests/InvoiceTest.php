<?php
declare(strict_types=1);

use App\Model\Invoice;
use PHPUnit\Framework\TestCase;

final class InvoiceTest extends TestCase
{
    public function testErrorOnInvalidInvoiceData1()
    {
        $data = ['Vendor 1','123456789','1000000257','1','','USD'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid invoice type: ' . implode(',', $data));

        new Invoice($data);
    }

    public function testErrorOnInvalidInvoiceData2()
    {
        $data = ['Vendor 1','123456789','1000000257','A','','USD','400'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid invoice type: ' . implode(',', $data));

        new Invoice($data);
    }

    public function testErrorOnInvalidInvoiceData3()
    {
        $data = ['Vendor 1','123456789','1000000257','4','','USD','400'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid invoice type: ' . implode(',', $data));

        new Invoice($data);
    }
}
