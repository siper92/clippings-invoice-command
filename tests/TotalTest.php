<?php
declare(strict_types=1);

use App\Model\Invoice;
use App\Model\Total;
use PHPUnit\Framework\TestCase;

final class TotalTest extends TestCase
{
    protected $total;

    protected function setUp(): void
    {
        $this->total = (new Total('Test', 'BGN'))
            ->add(100)
            ->add(100)
            ->subtract(34.65)
            ->subtract(0.346);
    }

    public function testTotalCalculation()
    {
        $this->assertEquals(165, $this->total->getTotal(), 'Total not calculated correctly');
    }

    public function testTotalToString()
    {
        $this->assertEquals('Test - 165 BGN', (string) $this->total, 'Total not printed in proper format');
    }
}
