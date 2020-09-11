<?php

namespace App\Service;

use App\Model\Currency;
use App\Model\Invoice;
use App\Model\Total;
use RuntimeException;

class InvoiceSummary
{
    /**
     * @var Currency[]
     */
    private $currencies;

    /**
     * @var Currency
     */
    private $defaultCurrency;

    /**
     * @var Invoice[]
     */
    private $invoices;

    public function setData(array $data): InvoiceSummary
    {
        $this->invoices = [];
        foreach ($data as $rowData) {
            $invoice = new Invoice($rowData);

            if ($invoice->getParent() && !isset($this->invoices[$invoice->getParent()])) {
                throw new RuntimeException("Missing parent invoice #{$invoice->getParent()} for invoice #{$invoice->getNumber()}");
            }

            $this->invoices[$invoice->getNumber()] = $invoice;
        }

        return $this;
    }

    /**
     * @param Currency[] $currencies
     * @return $this
     */
    public function setCurrencies(array $currencies): InvoiceSummary
    {
        foreach ($currencies as $currency) {
            if ($currency->isDefault()) {
                $this->defaultCurrency = $currency;
            }
            $this->currencies[$currency->getCode()] = $currency;
        }

        if (!$this->defaultCurrency) {
            throw new RuntimeException("No default currency supplied");
        }

        return $this;
    }

    /**
     * @param string|null $vatFilter
     * @param Currency|null $outputCurrency
     * @return Total[]
     */
    public function getTotals(?string $vatFilter = '', ?Currency $outputCurrency = null): array
    {
        if (!$outputCurrency) {
            $outputCurrency = $this->defaultCurrency;
        }

        $customerTotals = [];
        foreach ($this->invoices as $invoice) {
            $vat = $invoice->getVatNumber();
            if ($vatFilter && $vat != $vatFilter) {
                continue;
            }

            if (!isset($customerTotals[$vat])) {
                $customerTotals[$vat] = new Total($invoice->getCustomer(), $outputCurrency->getCode());
            }

            $total = $customerTotals[$vat];
            $invoiceTotal = $this->getInvoiceTotalInOutputCurrency($invoice, $outputCurrency);
            if ($invoice->isCredit()) {
                $total->subtract($invoiceTotal);
            } else {
                $total->add($invoiceTotal);
            }
        }

        return $customerTotals;
    }

    private function getInvoiceTotalInOutputCurrency(Invoice $invoice, Currency $outputCurrency): float
    {
        $total = $invoice->getTotal();
        $invoiceCurrency = $this->getCurrency($invoice->getCurrencyCode());

        if ($outputCurrency->getCode() != $invoiceCurrency->getCode()) {
            $total = $total * ($invoiceCurrency->getRate() /  $outputCurrency->getRate());
        }

        return round($total, 2); // no precision specifications
    }

    private function getCurrency(string $code): Currency
    {
        if (!isset($this->currencies[$code])) {
            throw new RuntimeException("Invalid or missing currency '{$code}'");
        }

        return $this->currencies[$code];
    }
}
