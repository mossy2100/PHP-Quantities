<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\Currencies;

use Mossy2100\Quantities\Currencies\CurrencyService;
use Mossy2100\Quantities\Currencies\ExchangeRateServices\FrankfurterService;
use Mossy2100\Quantities\Internal\Converter;
use Mossy2100\Quantities\Services\UnitService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Live tests for CurrencyService.
 *
 * These call the SIX Group ISO 4217 list and the Frankfurter exchange rate API (free, no API key), so they're slow
 * and need network access. Offline tests are in tests/NonCurrencies/Currencies.
 */
#[CoversClass(CurrencyService::class)]
final class CurrencyServiceTest extends TestCase
{
    /**
     * The test data directory path.
     */
    private const string TEST_DATA_DIR = __DIR__ . '/data';

    #region Setup

    protected function setUp(): void
    {
        // Point data files to the test directory so we don't overwrite real data.
        CurrencyService::setDataDir(self::TEST_DATA_DIR);
        CurrencyService::setExchangeRateService(new FrankfurterService());
    }

    protected function tearDown(): void
    {
        // Reset static state after each test.
        CurrencyService::setExchangeRateService(null);
        CurrencyService::resetDataDir();
        UnitService::reset();
        Converter::removeAllInstances();
    }

    #endregion

    #region Method refreshUnits() tests.

    /**
     * Test refreshUnits() fetches the ISO 4217 list and writes the data files.
     */
    public function testRefreshUnitsWritesDataFiles(): void
    {
        CurrencyService::deleteUnits();

        $data = CurrencyService::refreshUnits();

        $this->assertNotEmpty($data['currencies']);
        $this->assertFileExists(self::TEST_DATA_DIR . '/CurrencyUnits.php');
        $this->assertFileExists(self::TEST_DATA_DIR . '/CurrencyUnits.xml');
        $this->assertSame($data, CurrencyService::getUnits());
    }

    /**
     * Test refreshUnits() includes common currencies.
     */
    public function testRefreshUnitsContainsCommonCurrencies(): void
    {
        $codes = array_keys(CurrencyService::refreshUnits()['currencies']);

        foreach (['USD', 'EUR', 'GBP', 'AUD', 'JPY'] as $code) {
            $this->assertContains($code, $codes);
        }
    }

    /**
     * Test refreshUnits() stores SDR under its cleaned-up name, not the parenthesised ISO 4217 form.
     */
    public function testRefreshUnitsRenamesSdr(): void
    {
        $currencies = CurrencyService::refreshUnits()['currencies'];

        $this->assertSame('Special Drawing Right', $currencies['XDR']);
        $this->assertNotContains('SDR (Special Drawing Right)', $currencies);
    }

    #endregion

    #region Method refreshRates() tests.

    /**
     * Test refreshRates() fetches live rates and writes the data file.
     */
    public function testRefreshRatesWritesDataFile(): void
    {
        $data = CurrencyService::refreshRates();

        $this->assertSame('Frankfurter (ECB)', $data['serviceName']);
        $this->assertNotEmpty($data['definitions']);
        $this->assertFileExists(self::TEST_DATA_DIR . '/CurrencyConversions.php');
        $this->assertSame($data, CurrencyService::getConversions());
    }

    #endregion
}
