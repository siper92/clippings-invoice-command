<?php
declare(strict_types=1);

use App\Command;
use PHPUnit\Framework\TestCase;

final class CommandTest extends TestCase
{
    protected $args = [
        './console',
        'import',
        './data.csv',
        'EUR:1,USD:0.987,GBP:0.878',
        'GBP',
        '--vat=123456789',
    ];

    protected function setUp(): void
    {
        $this->args[2] = dirname(__DIR__) . '/data.csv';
    }

    public function testCanCreateCommand(): void
    {
        $this->assertInstanceOf(Command::class, new Command($this->args));
    }

    public function testCannotInstantiateClassWithLessArguments()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid arguments');

        $args = $this->args;
        array_pop($args);
        array_pop($args);
        new Command($args);
    }

    public function testCannotInstantiateClassWithInvalidImport()
    {
        $path = dirname(__DIR__) . '/unknown.csv';
        $this->args[2] = $path;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid import path: {$path}");

        new Command($this->args);
    }

    public function testCannotInstantiateClassWithInvalidRates1()
    {
        $this->args[3] = '';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Currency rates are required");

        new Command($this->args);
    }

    public function testCannotInstantiateClassWithInvalidRates2()
    {
        $this->args[3] = 'EUR:1,USD:0.987,GBP';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid currency rates format: EUR:1,USD:0.987,GBP,\n > proper format: CODE:RATE,CODE:RATE");

        new Command($this->args);
    }

    public function testCannotInstantiateClassWithInvalidOutputCurrency()
    {
        $this->args[4] = 'BGN';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Unsupported output currency: BGN");

        new Command($this->args);
    }

    public function testCannotInstantiateClassWithInvalidVatFilter()
    {
        $this->args[5] = '--vat0999209';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid vat filter argument: --vat0999209");

        new Command($this->args);
    }
}
