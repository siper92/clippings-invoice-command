<?php

namespace App;

use App\Model\Currency;
use App\Service\InvoiceSummary;
use InvalidArgumentException;

class Command
{
    /**
     * @var string
     */
    private $dataSourcePath;

    /**
     * @var Currency[]
     */
    private $currencyRates;

    /**
     * @var Currency
     */
    private $outputCurrencyCode;

    /**
     * @var string
     */
    private $vatFilter;

    public function __construct(array $cliArgs)
    {
        if (count($cliArgs) < 5) {
            throw new InvalidArgumentException('Invalid arguments');
        }

        $this->dataSourcePath = $this->parseImportPath($cliArgs[2]);
        $this->currencyRates = $this->parseCurrencyRates($cliArgs[3]);

        if (!isset($this->currencyRates[$cliArgs[4]])) {
            throw new InvalidArgumentException("Unsupported output currency: {$cliArgs[4]}");
        }
        $this->outputCurrencyCode = $this->currencyRates[$cliArgs[4]];

        if (isset($cliArgs[5])) {
            $this->vatFilter = $this->parseVatFilter($cliArgs[5]);
        }
    }


    private function parseImportPath(string $path): string
    {
        $dataSourcePath = realpath($path);
        if (!$dataSourcePath) {
            throw new InvalidArgumentException("Invalid import path: {$path}");
        }

        return $dataSourcePath;
    }

    /**
     * @param string $rates
     * @return Currency[]
     */
    private function parseCurrencyRates(string $rates): array
    {
        if (!$rates) {
            throw new InvalidArgumentException("Currency rates are required");
        }

        $currenciesData = explode(',', $rates);
        $currencies = [];

        foreach ($currenciesData as $currencyData) {
            $currencyData = explode(':', $currencyData);
            if (count($currencyData) < 2) {
                throw new InvalidArgumentException("Invalid currency rates format: {$rates},\n > proper format: CODE:RATE,CODE:RATE");
            }

            list($currencyCode, $rate) = $currencyData;
            $currencies[$currencyCode] = new Currency($currencyCode, $rate);
        }

        return $currencies;
    }

    private function parseVatFilter(string $vatFilter): string
    {
        if (strpos($vatFilter, '--vat=') === false) {
            throw new InvalidArgumentException("Invalid vat filter argument: {$vatFilter}");
        }

        return str_replace('--vat=', '', $vatFilter);
    }

    public function getInvoicesData(): array
    {
        $result = [];

        $source = file($this->dataSourcePath);
        foreach ($source as $i => $line) {
            if ($i == 0) {
                continue;
            }

            $result[] = str_getcsv($line);
        }

        return $result;
    }


    public function run()
    {
        $totalsService = (new InvoiceSummary())
            ->setData($this->getInvoicesData())
            ->setCurrencies($this->currencyRates);

        $totals = $totalsService->getTotals($this->vatFilter, $this->outputCurrencyCode);

        foreach ($totals as $total) {
            echo $total . PHP_EOL;
        }
    }
}
