<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\QuantityType;

use Mossy2100\Quantities\Currencies\CurrencyService;
use Mossy2100\Quantities\Internal\Converter;
use Mossy2100\Quantities\QuantityType\Money;
use Mossy2100\Quantities\Services\UnitService;
use Mossy2100\Quantities\Tests\NonCurrencies\Fixtures\FakeExchangeRateService;
use Mossy2100\Quantities\Tests\NonCurrencies\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Offline tests for the Money quantity type.
 *
 * Currency units come from the snapshot bundled with the package, and rates from a fake exchange rate service, so
 * these never call a live API. Tests of live currency conversion are in tests/Currencies.
 */
#[CoversClass(Money::class)]
final class MoneyTest extends TestCase
{
    use ArrayShapeTrait;

    #region Setup

    /**
     * The temporary data directory for the current test.
     */
    private string $dataDir;

    protected function setUp(): void
    {
        // Use a fresh, empty data directory, so currency units come from the bundled snapshot and there are no rates.
        $this->dataDir = sys_get_temp_dir() . '/quantities-money-test-' . bin2hex(random_bytes(6));
        CurrencyService::setDataDir($this->dataDir);
        UnitService::reset();
        Converter::removeAllInstances();
    }

    protected function tearDown(): void
    {
        // Reset static state.
        CurrencyService::setExchangeRateService(null);
        CurrencyService::setLocale(null);
        CurrencyService::resetDataDir();
        UnitService::reset();
        Converter::removeAllInstances();

        // Remove the temporary data directory.
        foreach (glob($this->dataDir . '/*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->dataDir);
    }

    #endregion

    #region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = Money::getUnitDefinitions();
        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getUnitDefinitions() contains common currencies.
     */
    public function testGetUnitDefinitionsContainsCommonCurrencies(): void
    {
        $units = Money::getUnitDefinitions();

        $symbols = array_map(
            static fn (array $def) => $def['asciiSymbol'],
            $units
        );

        $this->assertContains('USD', $symbols);
        $this->assertContains('EUR', $symbols);
        $this->assertContains('GBP', $symbols);
        $this->assertContains('AUD', $symbols);
        $this->assertContains('JPY', $symbols);
    }

    /**
     * Test getConversionDefinitions() returns valid conversion definitions once rates have been fetched.
     */
    public function testGetConversionDefinitionsReturnsValidArray(): void
    {
        CurrencyService::setExchangeRateService(new FakeExchangeRateService());
        CurrencyService::refreshRates();

        $conversions = Money::getConversionDefinitions();
        $this->assertValidConversionDefinitionsShape($conversions);
    }

    /**
     * Test getConversionDefinitions() returns an empty array when there are no local rates.
     */
    public function testGetConversionDefinitionsReturnsEmptyWhenNoRates(): void
    {
        $this->assertSame([], Money::getConversionDefinitions());
    }

    #endregion

    #region Construction tests

    /**
     * Test constructing a Money quantity with USD.
     */
    public function testConstructWithUsd(): void
    {
        $money = new Money(100, 'USD');

        $this->assertInstanceOf(Money::class, $money);
        $this->assertSame(100.0, $money->value);
        $this->assertSame('USD', $money->compoundUnit->asciiSymbol);
    }

    /**
     * Test constructing a Money quantity with EUR.
     */
    public function testConstructWithEur(): void
    {
        $money = new Money(50, 'EUR');

        $this->assertSame(50.0, $money->value);
        $this->assertSame('EUR', $money->compoundUnit->asciiSymbol);
    }

    /**
     * Test constructing with zero value.
     */
    public function testConstructWithZero(): void
    {
        $money = new Money(0, 'USD');

        $this->assertSame(0.0, $money->value);
    }

    /**
     * Test constructing with negative value.
     */
    public function testConstructWithNegative(): void
    {
        $money = new Money(-25.50, 'GBP');

        $this->assertSame(-25.50, $money->value);
    }

    #endregion

    #region __toString() tests

    /**
     * Test __toString() with an explicit locale.
     */
    public function testToStringWithLocale(): void
    {
        CurrencyService::setLocale('en_US');
        $money = new Money(1234.56, 'USD');

        $result = (string) $money;

        // en_US locale should format as "$1,234.56".
        $this->assertStringContainsString('1', $result);
        $this->assertStringContainsString('234', $result);
        $this->assertStringContainsString('56', $result);
    }

    /**
     * Test __toString() with a different locale.
     */
    public function testToStringWithDifferentLocale(): void
    {
        CurrencyService::setLocale('de_DE');
        $money = new Money(1234.56, 'EUR');

        $result = (string) $money;

        // de_DE locale should format with comma as decimal separator.
        $this->assertNotEmpty($result);
    }

    /**
     * Test __toString() falls back to format() when locale is null.
     */
    public function testToStringFallsBackToFormatWhenNoLocale(): void
    {
        CurrencyService::setLocale(null);

        // Temporarily set PHP's default locale to empty to force fallback.
        $originalLocale = setlocale(LC_ALL, '0');
        setlocale(LC_ALL, 'C');

        try {
            $money = new Money(100, 'USD');
            $result = (string) $money;

            // Should fall back to format() which produces something like "100 USD".
            $this->assertNotEmpty($result);
            $this->assertStringContainsString('100', $result);
        } finally {
            if ($originalLocale !== false) {
                setlocale(LC_ALL, $originalLocale);
            }
        }
    }

    /**
     * Test __toString() with zero value.
     */
    public function testToStringWithZero(): void
    {
        CurrencyService::setLocale('en_US');
        $money = new Money(0, 'USD');

        $result = (string) $money;

        $this->assertStringContainsString('0', $result);
    }

    /**
     * Test __toString() with negative value.
     */
    public function testToStringWithNegativeValue(): void
    {
        CurrencyService::setLocale('en_US');
        $money = new Money(-50, 'USD');

        $result = (string) $money;

        $this->assertNotEmpty($result);
    }

    #endregion

    #region Addition tests

    /**
     * Test adding money in the same currency.
     */
    public function testAddSameCurrency(): void
    {
        $a = new Money(100, 'USD');
        $b = new Money(50, 'USD');
        $result = $a->add($b);

        $this->assertInstanceOf(Money::class, $result);
        $this->assertSame(150.0, $result->value);
        $this->assertSame('USD', $result->compoundUnit->asciiSymbol);
    }

    #endregion

    #region Parse tests

    /**
     * Test parsing a USD string.
     */
    public function testParseUsd(): void
    {
        $money = Money::parse('100 USD');

        $this->assertInstanceOf(Money::class, $money);
        $this->assertSame(100.0, $money->value);
        $this->assertSame('USD', $money->compoundUnit->asciiSymbol);
    }

    /**
     * Test parsing a EUR string.
     */
    public function testParseEur(): void
    {
        $money = Money::parse('250.75 EUR');

        $this->assertSame(250.75, $money->value);
        $this->assertSame('EUR', $money->compoundUnit->asciiSymbol);
    }

    /**
     * Test parsing a negative value.
     */
    public function testParseNegative(): void
    {
        $money = Money::parse('-50 GBP');

        $this->assertSame(-50.0, $money->value);
        $this->assertSame('GBP', $money->compoundUnit->asciiSymbol);
    }

    #endregion
}
