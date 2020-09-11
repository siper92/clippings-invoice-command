<?php
declare(strict_types=1);

use App\Model\Currency;
use App\Model\Invoice;
use App\Model\Total;
use App\Service\InvoiceSummary;
use PHPUnit\Framework\TestCase;

final class InvoiceSummaryTest extends TestCase
{
    public function testErrorOnMissingParent()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Missing parent invoice #1000000250 for invoice #1000000260");

        (new InvoiceSummary())
            ->setData([
                ['Vendor 1','123456789','1000000257','1','','USD','400'],
                ['Vendor 2','987654321','1000000258','1','','EUR','900'],
                ['Vendor 3','123465123','1000000259','1','','GBP','1300'],
                ['Vendor 1','123456789','1000000260','2','1000000250','EUR','100'],
                ['Vendor 1','123456789','1000000261','3','1000000257','GBP','50'],
                ['Vendor 2','987654321','1000000262','2','1000000258','USD','200'],
                ['Vendor 3','123465123','1000000263','3','1000000259','EUR','100'],
                ['Vendor 1','123456789','1000000264','1','','EUR','1600'],
            ]);
    }

    public function testErrorOnMissingDefaultCurrency()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("No default currency supplied");

        (new InvoiceSummary())
            ->setCurrencies([
                new Currency('EUR', 1.1),
                new Currency('USD', 0.5),
            ]);
    }

    public function testErrorOnMissingCurrency()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Invalid or missing currency 'GBP'");

        $service = (new InvoiceSummary())
            ->setCurrencies([
                new Currency('EUR', 1),
                new Currency('USD', 0.5),
            ]);

        $this->invokeMethod(
            $service,
            'getCurrency',
            ['GBP']
        );
    }

    public function testGetTotalsWiltOutputCurrency()
    {
        $service = (new InvoiceSummary())
            ->setData([
                ['Vendor 1','123456789','1000000256','1','','USD','200'],
                ['Vendor 1','123456789','1000000257','1','','USD','200'],
                ['Vendor 2','987654321','1000000258','1','','USD','800'],
                ['Vendor 3','123465123','1000000259','1','','USD','1000'],
                ['Vendor 3','123465123','1000000260','1','','USD','1100'],
                ['Vendor 3','123465123','1000000261','2','','USD','600'],
                ['Vendor 3','123465123','1000000262','3','','USD','500'],
            ])
            ->setCurrencies([
                new Currency('EUR', 1),
                new Currency('USD', 0.5),
                new Currency('BGN', 2),
            ]);

        $totals = $service->getTotals(null, new Currency('BGN', 2));

        // 400 USD = 200 EUR = 100 BGN
        // 800 USD = 400 EUR = 200 BGN
        // 2000 USD = 1000 EUR = 500 BGN
        $this->assertEquals(
            [
                '123456789' => 100.0,
                '987654321' => 200.0,
                '123465123' => 500.0
            ],
            array_map(function (Total $val) {
                return $val->getTotal();
            }, $totals),
            'Cannot calculate totals correctly'
        );
    }

    public function testGetTotalsWiltAndMixedInvoiceCurrencies()
    {
        $service = (new InvoiceSummary())
            ->setData([
                ['Vendor 1','123456789','1000000256','1','','USD','200'], // 100 EUR
                ['Vendor 1','123456789','1000000257','1','','USD','200'], // 100 EUR
                ['Vendor 1','123456789','1000000258','1','','EUR','100'], // 100 EUR
                ['Vendor 1','123456789','1000000259','1','','BGN','50'], // 100 EUR
                ['Vendor 1','123456789','1000000261','2','','BGN','60'], // -120 EURO
                ['Vendor 1','123456789','1000000262','3','','EUR','20'], // +20 EUR
            ])
            ->setCurrencies([
                new Currency('EUR', 1),
                new Currency('USD', 0.5),
                new Currency('BGN', 2),
            ]);

        // 300 EUR = 150 BGN
        $totals = $service->getTotals(null, new Currency('BGN', 2));

        $this->assertEquals(
            [
                '123456789' => 150.0,
            ],
            array_map(function (Total $val) {
                return $val->getTotal();
            }, $totals),
            'Cannot calculate totals correctly'
        );
    }

    public function testGetTotalsWithoutOutputCurrency()
    {
        $service = (new InvoiceSummary())
            ->setData([
                ['Vendor 1','123456789','1000000256','1','','USD','200'],
                ['Vendor 1','123456789','1000000257','1','','USD','200'],
                ['Vendor 2','987654321','1000000258','1','','USD','800'],
                ['Vendor 3','123465123','1000000259','1','','USD','1000'],
                ['Vendor 3','123465123','1000000260','1','','USD','1000'],
            ])
            ->setCurrencies([
                new Currency('EUR', 1),
                new Currency('USD', 0.5),
                new Currency('BGN', 2),
            ]);

        $totals = $service->getTotals();

        // 400 USD = 200 EUR
        // 800 USD = 400 EUR
        // 2000 USD = 1000 EUR
        $this->assertEquals(
            [
                '123456789' => 200.0,
                '987654321' => 400.0,
                '123465123' => 1000.0
            ],
            array_map(function (Total $val) {
                return $val->getTotal();
            }, $totals),
            'Cannot calculate totals correctly'
        );
    }

    public function testGetTotalsWiltOutputCurrencyAndFilter()
    {
        $service = (new InvoiceSummary())
            ->setData([
                ['Vendor 1','123456789','1000000256','1','','USD','200'],
                ['Vendor 1','123456789','1000000257','1','','USD','200'],
                ['Vendor 2','987654321','1000000258','1','','USD','800'],
                ['Vendor 3','123465123','1000000259','1','','USD','1000'],
                ['Vendor 3','123465123','1000000260','1','','USD','1000'],
            ])
            ->setCurrencies([
                new Currency('EUR', 1),
                new Currency('USD', 0.5),
                new Currency('BGN', 2),
            ]);

        $totals = $service->getTotals('123456789', new Currency('BGN', 2));

        // 400 USD = 200 EUR = 100 BGN
        $this->assertEquals(['123456789' => 100.0], array_map(function (Total $val) {
            return $val->getTotal();
        }, $totals), 'Cannot calculate totals correctly with vat number filter');
    }

    public function testHandleCreditsLargerThanTheInvoice()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Invoice cannot have negative value!");

        $service = (new InvoiceSummary())
            ->setData([
                ['Vendor 1','123456789','1000000256','1','','USD','200'],
                ['Vendor 1','123456789','1000000257','1','','USD','200'],
                ['Vendor 1','123456789','1000000258','1','','USD','300'],
                ['Vendor 1','123456789','1000000259','2','','USD','300'],
                ['Vendor 1','123456789','1000000260','2','','USD','401'],
            ])
            ->setCurrencies([
                new Currency('EUR', 1),
                new Currency('USD', 0.5),
                new Currency('BGN', 2),
            ]);

        $service->getTotals(null, new Currency('BGN', 2));
    }

    public function testProperCurrencyConversionToOutputCurrency()
    {
        $outputCurrency = new Currency('BGN', 2); // 2 BGN for 1 EUR
        $service = (new InvoiceSummary())
            ->setCurrencies([
                new Currency('EUR', 1),
                new Currency('USD', 0.5),
                $outputCurrency,
            ]);

        $invoice = new Invoice(['Vendor 1','123456789','1000000257','1','','USD','400']);

        // 400 USD == 200 EUR == 100 BGN
        $this->assertEquals(
            100,
            $this->invokeMethod(
                $service,
                'getInvoiceTotalInOutputCurrency',
                [$invoice, $outputCurrency]
            ),
            'Invalid conversion to output currency'
        );
    }

    public function testProperCurrencyConversionToOutputCurrency2()
    {
        $outputCurrency = new Currency('GBP', 0.878);
        $service = (new InvoiceSummary())
            ->setCurrencies([
                new Currency('EUR', 1),
                new Currency('USD', 0.987),
                $outputCurrency,
            ]);

        $invoice = new Invoice(['Vendor 1','123456789','1000000257','1','','USD','400']);

        // 400 USD * 0.987 == 394.8 EUR * (1 / 0.878) == 449.66 GBP
        $this->assertEquals(
            449.66,
            $this->invokeMethod(
                $service,
                'getInvoiceTotalInOutputCurrency',
                [$invoice, $outputCurrency]
            ),
            'Invalid conversion to output currency'
        );
    }

    public function testNoConversionIfCurrencyIsTheSame()
    {
        $outputCurrency = new Currency('USD', 0.5);
        $service = (new InvoiceSummary())
            ->setCurrencies([
                new Currency('EUR', 1),
                $outputCurrency,
                new Currency('BGN', 2),
            ]);

        $invoice = new Invoice(['Vendor 1','123456789','1000000257','1','','USD','400']);

        // 400 USD == 200 EUR == 100 BGN
        $this->assertEquals(
            400,
            $this->invokeMethod(
                $service,
                'getInvoiceTotalInOutputCurrency',
                [$invoice, $outputCurrency]
            ),
            'Invalid conversion to when currency is the same'
        );
    }

    public function invokeMethod(InvoiceSummary &$object, string $methodName, array $parameters = array())
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
