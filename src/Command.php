<?php

namespace App;

use App\Model\Currency;
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
     * @var string
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
        $this->outputCurrencyCode = $cliArgs[4];
        if (!isset($this->currencyRates[$this->outputCurrencyCode])) {
            throw new InvalidArgumentException("Unsupported output currency: {$this->outputCurrencyCode}");
        }

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

    private function parseCurrencyRates(string $rates): array
    {
        $currencies = [];

        $currenciesData = explode(',', $rates);
        foreach ($currenciesData as $currencyData) {
            $currencyData = explode(':', $currencyData);
            if (count($currencyData) < 2) {
                throw new InvalidArgumentException("Invalid currency rate format: {$rates},\n > proper format: CODE:RATE,CODE:RATE");
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


    public function run()
    {
        echo 'test';
    }
}
