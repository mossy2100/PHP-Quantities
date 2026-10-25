<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Currencies;

use DateTimeImmutable;
use DomainException;
use LogicException;
use Mossy2100\Core\Exceptions\FormatException;
use Mossy2100\Quantities\Currencies\CurrencyService;
use Mossy2100\Quantities\Internal\Converter;
use Mossy2100\Quantities\QuantityType\Length;
use Mossy2100\Quantities\QuantityType\Money;
use Mossy2100\Quantities\Services\UnitService;
use Mossy2100\Quantities\Tests\NonCurrencies\Fixtures\FakeExchangeRateService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClassConstant;
use ReflectionProperty;
use RuntimeException;

/**
 * Offline tests for CurrencyService.
 *
 * These use a fake exchange rate service and a temporary data directory, so they never call a live API. Tests that
 * need live APIs are in tests/Currencies.
 */
#[CoversClass(CurrencyService::class)]
#[CoversClass(Converter::class)]
#[CoversClass(Money::class)]
final class CurrencyServiceTest extends TestCase
{
    /**
     * The temporary data directory for the current test.
     */
    private string $dataDir;

    #region Setup

    protected function setUp(): void
    {
        // Use a fresh, empty data directory for each test, so no refreshed data is present.
        $this->dataDir = sys_get_temp_dir() . '/quantities-currency-test-' . bin2hex(random_bytes(6));
        CurrencyService::setDataDir($this->dataDir);

        // Start with fresh registries, so currency units and conversions come from the current data directory.
        UnitService::reset();
        Converter::removeAllInstances();
    }

    protected function tearDown(): void
    {
        // Reset static state after each test.
        CurrencyService::setExchangeRateService(null);
        CurrencyService::setLocale(null);
        CurrencyService::setAutoRefreshTtl(null);
        CurrencyService::resetDataDir();
        $defaultUrl = new ReflectionClassConstant(CurrencyService::class, 'ISO_4217_URL')->getValue();
        assert(is_string($defaultUrl));
        self::setIso4217Url($defaultUrl);
        UnitService::reset();
        Converter::removeAllInstances();

        // Remove the temporary data directory.
        if (is_dir($this->dataDir)) {
            chmod($this->dataDir, 0755);
            foreach (glob($this->dataDir . '/*') ?: [] as $file) {
                unlink($file);
            }
            rmdir($this->dataDir);
        }
    }

    #endregion

    #region Default behaviour tests.

    /**
     * Test that non-currency conversions work with no currency data and no exchange rate service.
     */
    public function testNonCurrencyConversionNeedsNoCurrencyData(): void
    {
        $length = new Length(1, 'm');

        $this->assertEqualsWithDelta(3.28084, $length->to('ft')->value, 1e-5);
        $this->assertSame([], glob($this->dataDir . '/*'));
    }

    /**
     * Test that currency units are available without fetching anything.
     */
    public function testCurrencyUnitsAvailableWithoutFetching(): void
    {
        $service = new FakeExchangeRateService();
        CurrencyService::setExchangeRateService($service);

        $money = new Money(5, 'USD');

        $this->assertSame('USD', $money->compoundUnit->asciiSymbol);
        $this->assertSame(0, $service->fetchCount);
    }

    /**
     * Test that a currency conversion with no local rates fails in the usual way when auto-refresh is off.
     */
    public function testCurrencyConversionWithoutRatesFails(): void
    {
        $service = new FakeExchangeRateService();
        CurrencyService::setExchangeRateService($service);

        try {
            new Money(5, 'USD')->to('EUR');
            $this->fail('Expected LogicException.');
        } catch (LogicException $e) {
            $this->assertStringStartsWith("No conversion path found between 'USD' and 'EUR'", $e->getMessage());
        }

        $this->assertSame(0, $service->fetchCount);
    }

    /**
     * Test that a currency conversion uses the local rates, however old, when auto-refresh is off.
     */
    public function testCurrencyConversionUsesOldRatesWhenAutoRefreshOff(): void
    {
        $service = new FakeExchangeRateService();
        CurrencyService::setExchangeRateService($service);
        $this->writeConversionsFile('2000-01-01 00:00:00 UTC', 'Fake', [['USD', 'EUR', 0.5]]);

        $this->assertEqualsWithDelta(50.0, new Money(100, 'USD')->to('EUR')->value, 1e-9);
        $this->assertSame(0, $service->fetchCount);
    }

    #endregion

    #region Method getUnits() tests.

    /**
     * Test getUnits() returns the bundled snapshot when the data directory has no units file.
     */
    public function testGetUnitsReturnsSnapshotWhenNoRefreshedCopy(): void
    {
        $data = CurrencyService::getUnits();

        $this->assertSame(self::snapshotWhenFetched(), $data['whenFetched']);
        $this->assertContains('USD', $data['currencies']);
        $this->assertContains('EUR', $data['currencies']);
        $this->assertSame('XDR', $data['currencies']['Special Drawing Right']);
    }

    /**
     * Test getUnits() returns the refreshed copy in the data directory when it's newer than the snapshot.
     */
    public function testGetUnitsReturnsRefreshedCopyWhenNewer(): void
    {
        $this->writeDataFile('CurrencyUnits.php', [
            'whenFetched' => '2999-01-01 00:00:00 UTC',
            'currencies' => ['US Dollar' => 'USD'],
        ]);

        $data = CurrencyService::getUnits();

        $this->assertSame('2999-01-01 00:00:00 UTC', $data['whenFetched']);
        $this->assertSame(['US Dollar' => 'USD'], $data['currencies']);
    }

    /**
     * Test getUnits() returns the snapshot when it's newer than the refreshed copy, e.g. after a package upgrade.
     */
    public function testGetUnitsReturnsSnapshotWhenNewer(): void
    {
        $this->writeDataFile('CurrencyUnits.php', [
            'whenFetched' => '2000-01-01 00:00:00 UTC',
            'currencies' => ['US Dollar' => 'USD'],
        ]);

        $this->assertSame(self::snapshotWhenFetched(), CurrencyService::getUnits()['whenFetched']);
    }

    /**
     * Test getUnits() falls back to the snapshot when the refreshed copy has a syntax error.
     */
    public function testGetUnitsFallsBackToSnapshotOnParseError(): void
    {
        file_put_contents($this->dataDir . '/CurrencyUnits.php', '<?php this is not valid php;');

        $this->assertSame(self::snapshotWhenFetched(), CurrencyService::getUnits()['whenFetched']);
    }

    /**
     * Test getUnits() falls back to the snapshot when the refreshed copy doesn't return a valid array.
     */
    public function testGetUnitsFallsBackToSnapshotWhenFileReturnsNonArray(): void
    {
        file_put_contents($this->dataDir . '/CurrencyUnits.php', '<?php return "not an array";');

        $this->assertSame(self::snapshotWhenFetched(), CurrencyService::getUnits()['whenFetched']);
    }

    /**
     * Test getUnits() falls back to the snapshot when the refreshed copy has an invalid currency entry.
     */
    public function testGetUnitsFallsBackToSnapshotWhenCurrencyInvalid(): void
    {
        $this->writeDataFile('CurrencyUnits.php', [
            'whenFetched' => '2026-10-25 00:00:00 UTC',
            'currencies' => ['US Dollar' => 42],
        ]);

        $this->assertSame(self::snapshotWhenFetched(), CurrencyService::getUnits()['whenFetched']);
    }

    #endregion

    #region Method getConversions() tests.

    /**
     * Test getConversions() returns null when there are no local rates.
     */
    public function testGetConversionsReturnsNullWhenNoFile(): void
    {
        $this->assertNull(CurrencyService::getConversions());
    }

    /**
     * Test getConversions() returns the local rates.
     */
    public function testGetConversionsReturnsLocalRates(): void
    {
        $this->writeConversionsFile('2026-10-25 00:00:00 UTC', 'Fake', [['USD', 'EUR', 0.9]]);

        $data = CurrencyService::getConversions();

        $this->assertNotNull($data);
        $this->assertSame('Fake', $data['serviceName']);
        $this->assertSame([['USD', 'EUR', 0.9]], $data['definitions']);
    }

    /**
     * Test getConversions() returns null when the rates file has a syntax error.
     */
    public function testGetConversionsReturnsNullOnParseError(): void
    {
        file_put_contents($this->dataDir . '/CurrencyConversions.php', '<?php this is not valid php;');

        $this->assertNull(CurrencyService::getConversions());
    }

    /**
     * Test getConversions() returns null when the rates file doesn't return an array.
     */
    public function testGetConversionsReturnsNullWhenFileReturnsNonArray(): void
    {
        file_put_contents($this->dataDir . '/CurrencyConversions.php', '<?php return 42;');

        $this->assertNull(CurrencyService::getConversions());
    }

    /**
     * Test getConversions() returns null when a definition has the wrong shape.
     */
    public function testGetConversionsReturnsNullWhenDefinitionInvalid(): void
    {
        $this->writeConversionsFile('2026-10-25 00:00:00 UTC', 'Fake', [['USD', 'EUR']]);

        $this->assertNull(CurrencyService::getConversions());
    }

    /**
     * Test getConversions() accepts a whole-number rate written as an int, and returns it as a float.
     */
    public function testGetConversionsAcceptsIntegerRate(): void
    {
        $this->writeConversionsFile('2026-10-25 00:00:00 UTC', 'Fake', [['USD', 'JPY', 150], ['USD', 'EUR', 0.9]]);

        $data = CurrencyService::getConversions();

        $this->assertNotNull($data);
        $this->assertSame([['USD', 'JPY', 150.0], ['USD', 'EUR', 0.9]], $data['definitions']);
    }

    /**
     * Test getConversions() returns null when a rate isn't a number.
     */
    public function testGetConversionsReturnsNullWhenRateNotNumber(): void
    {
        $this->writeConversionsFile('2026-10-25 00:00:00 UTC', 'Fake', [['USD', 'EUR', '0.9']]);

        $this->assertNull(CurrencyService::getConversions());
    }

    /**
     * Test rates with a whole-number rate stay fresh, so auto-refresh doesn't refetch them on every conversion.
     */
    public function testAutoRefreshKeepsRatesWithIntegerRate(): void
    {
        $this->writeConversionsFile(gmdate('Y-m-d H:i:s') . ' UTC', 'Fake', [['USD', 'JPY', 150]]);
        $service = new FakeExchangeRateService();
        CurrencyService::init($service, autoRefreshTtl: 3600);

        $this->assertSame(1500.0, new Money(10, 'USD')->to('JPY')->value);
        $this->assertSame(0, $service->fetchCount);
    }

    #endregion

    #region Method refreshRates() tests.

    /**
     * Test refreshRates() throws when no exchange rate service is configured.
     */
    public function testRefreshRatesThrowsWithoutService(): void
    {
        $this->expectException(LogicException::class);
        CurrencyService::refreshRates();
    }

    /**
     * Test refreshRates() fetches the rates, writes them, and makes them available.
     */
    public function testRefreshRatesWritesAndLoadsRates(): void
    {
        $service = new FakeExchangeRateService();
        CurrencyService::setExchangeRateService($service);

        $before = time();
        $data = CurrencyService::refreshRates();

        $this->assertSame(1, $service->fetchCount);
        $this->assertSame('Fake', $data['serviceName']);
        $this->assertFileExists(CurrencyService::getConversionsFilePath());
        $this->assertSame($data, CurrencyService::getConversions());
        $this->assertGreaterThanOrEqual($before, CurrencyService::getRatesFetchedAt()?->getTimestamp());
        $this->assertEqualsWithDelta(90.0, new Money(100, 'USD')->to('EUR')->value, 1e-9);
    }

    /**
     * Test refreshRates() replaces conversions loaded from older rates.
     */
    public function testRefreshRatesReplacesOldRates(): void
    {
        $this->writeConversionsFile('2000-01-01 00:00:00 UTC', 'Fake', [['USD', 'EUR', 0.5]]);
        $this->assertEqualsWithDelta(50.0, new Money(100, 'USD')->to('EUR')->value, 1e-9);

        CurrencyService::setExchangeRateService(new FakeExchangeRateService());
        CurrencyService::refreshRates();

        $this->assertEqualsWithDelta(90.0, new Money(100, 'USD')->to('EUR')->value, 1e-9);
    }

    /**
     * Test refreshRates() throws when the rates file can't be written.
     */
    public function testRefreshRatesThrowsWhenFileNotWritable(): void
    {
        if (function_exists('posix_getuid') && posix_getuid() === 0) {
            $this->markTestSkipped('File permissions are not enforced for root.');
        }

        CurrencyService::setExchangeRateService(new FakeExchangeRateService());
        chmod($this->dataDir, 0555);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to write file:');
        CurrencyService::refreshRates();
    }

    /**
     * Test refreshRates() throws when the data directory can't be created.
     */
    public function testRefreshRatesThrowsWhenDataDirCannotBeCreated(): void
    {
        CurrencyService::setExchangeRateService(new FakeExchangeRateService());
        chmod($this->dataDir, 0555);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to create directory:');
        CurrencyService::setDataDir($this->dataDir . '/sub');
    }

    #endregion

    #region Method refreshRatesIfStale() tests.

    /**
     * Test auto-refresh fetches the rates before a conversion when there are none.
     */
    public function testAutoRefreshFetchesWhenNoRates(): void
    {
        $service = new FakeExchangeRateService();
        CurrencyService::init($service, autoRefreshTtl: 3600);

        $this->assertEqualsWithDelta(90.0, new Money(100, 'USD')->to('EUR')->value, 1e-9);
        $this->assertSame(1, $service->fetchCount);
    }

    /**
     * Test auto-refresh doesn't fetch when the local rates are fresh.
     */
    public function testAutoRefreshSkipsFetchWhenRatesFresh(): void
    {
        $service = new FakeExchangeRateService();
        CurrencyService::init($service, autoRefreshTtl: 3600);
        CurrencyService::refreshRates();

        new Money(100, 'USD')->to('EUR');
        new Money(100, 'USD')->to('GBP');

        $this->assertSame(1, $service->fetchCount);
    }

    /**
     * Test auto-refresh fetches when the local rates are older than the TTL.
     */
    public function testAutoRefreshFetchesWhenRatesStale(): void
    {
        $this->writeConversionsFile('2000-01-01 00:00:00 UTC', 'Fake', [['USD', 'EUR', 0.5]]);
        $service = new FakeExchangeRateService();
        CurrencyService::init($service, autoRefreshTtl: 3600);

        $this->assertEqualsWithDelta(90.0, new Money(100, 'USD')->to('EUR')->value, 1e-9);
        $this->assertSame(1, $service->fetchCount);
    }

    /**
     * Test auto-refresh fetches when the local rates came from a different service.
     */
    public function testAutoRefreshFetchesWhenServiceChanged(): void
    {
        $this->writeConversionsFile(gmdate('Y-m-d H:i:s') . ' UTC', 'Other', [['USD', 'EUR', 0.5]]);
        $service = new FakeExchangeRateService();
        CurrencyService::init($service, autoRefreshTtl: 3600);

        $this->assertEqualsWithDelta(90.0, new Money(100, 'USD')->to('EUR')->value, 1e-9);
        $this->assertSame(1, $service->fetchCount);
    }

    /**
     * Test refreshRatesIfStale() reads the local rates' metadata if nothing has loaded it yet.
     */
    public function testRefreshRatesIfStaleLoadsLocalRatesMetadata(): void
    {
        $this->writeConversionsFile(gmdate('Y-m-d H:i:s') . ' UTC', 'Fake', [['USD', 'EUR', 0.5]]);
        $service = new FakeExchangeRateService();
        CurrencyService::init($service, autoRefreshTtl: 3600);

        CurrencyService::refreshRatesIfStale();

        $this->assertSame(0, $service->fetchCount);
        $this->assertNotNull(CurrencyService::getRatesFetchedAt());
    }

    /**
     * Test a failed auto-refresh throws rather than using the cached rates.
     */
    public function testAutoRefreshFailureThrows(): void
    {
        $this->writeConversionsFile('2000-01-01 00:00:00 UTC', 'Fake', [['USD', 'EUR', 0.5]]);
        CurrencyService::init(new FakeExchangeRateService(fail: true), autoRefreshTtl: 3600);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Fake exchange rate service failure.');
        new Money(100, 'USD')->to('EUR');
    }

    /**
     * Test auto-refresh throws when no exchange rate service is configured.
     */
    public function testAutoRefreshWithoutServiceThrows(): void
    {
        CurrencyService::setAutoRefreshTtl(3600);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The exchange rate service is not configured.');
        new Money(100, 'USD')->to('EUR');
    }

    /**
     * Test auto-refresh doesn't affect non-currency conversions.
     */
    public function testAutoRefreshIgnoresNonCurrencyConversions(): void
    {
        $service = new FakeExchangeRateService();
        CurrencyService::init($service, autoRefreshTtl: 3600);

        new Length(1, 'm')->to('ft');

        $this->assertSame(0, $service->fetchCount);
    }

    #endregion

    #region Method getRatesFetchedAt() tests.

    /**
     * Test getRatesFetchedAt() returns null when there are no local rates.
     */
    public function testGetRatesFetchedAtReturnsNullWhenNoRates(): void
    {
        $this->assertNull(CurrencyService::getRatesFetchedAt());
    }

    /**
     * Test getRatesFetchedAt() returns the time recorded in the rates file.
     */
    public function testGetRatesFetchedAtReadsRatesFile(): void
    {
        $this->writeConversionsFile('2026-10-25 01:02:03 UTC', 'Fake', [['USD', 'EUR', 0.9]]);

        $this->assertEquals(
            new DateTimeImmutable('2026-10-25 01:02:03 UTC'),
            CurrencyService::getRatesFetchedAt()
        );
    }

    #endregion

    #region Method deleteConversions() tests.

    /**
     * Test deleteConversions() removes the rates file, and is idempotent.
     */
    public function testDeleteConversionsRemovesFile(): void
    {
        CurrencyService::setExchangeRateService(new FakeExchangeRateService());
        CurrencyService::refreshRates();

        CurrencyService::deleteConversions();
        CurrencyService::deleteConversions();

        $this->assertFileDoesNotExist(CurrencyService::getConversionsFilePath());
        $this->assertNull(CurrencyService::getRatesFetchedAt());
    }

    #endregion

    #region Method refreshUnits() tests.

    /**
     * Test refreshUnits() filters and sorts the ISO 4217 currencies, writes the data files, and loads the new units.
     */
    public function testRefreshUnitsWritesFilteredCurrencies(): void
    {
        self::setIso4217Url(__DIR__ . '/../Fixtures/Iso4217Sample.xml');

        $data = CurrencyService::refreshUnits();

        // Funds, XB* bond units, XTS, XXX and entries without a code are skipped, each currency appears once, and SDR
        // is renamed.
        $this->assertSame([
            'Australian Dollar' => 'AUD',
            'Boliviano' => 'BOB',
            'Euro' => 'EUR',
            'Special Drawing Right' => 'XDR',
            'Test Currency' => 'XQZ',
        ], $data['currencies']);
        $this->assertFileExists(CurrencyService::getXmlFilePath());
        $this->assertSame($data, CurrencyService::getUnits());
        $this->assertSame('Test Currency', UnitService::getBySymbol('XQZ')?->name);
    }

    /**
     * Test refreshUnits() throws when the ISO 4217 XML can't be fetched.
     */
    public function testRefreshUnitsThrowsWhenFetchFails(): void
    {
        $url = __DIR__ . '/no-such-file.xml';
        self::setIso4217Url($url);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Failed to fetch ISO 4217 XML from '$url'.");
        CurrencyService::refreshUnits();
    }

    /**
     * Test refreshUnits() throws when the response isn't valid XML.
     */
    public function testRefreshUnitsThrowsForInvalidXml(): void
    {
        self::setIso4217Url('data:text/plain,not xml');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to parse ISO 4217 XML.');
        CurrencyService::refreshUnits();
    }

    #endregion

    #region Method deleteUnits() tests.

    /**
     * Test deleteUnits() removes the refreshed units files, and is idempotent.
     */
    public function testDeleteUnitsRemovesFiles(): void
    {
        file_put_contents(CurrencyService::getXmlFilePath(), '<ISO_4217/>');
        $this->writeDataFile('CurrencyUnits.php', [
            'whenFetched' => '2026-10-25 00:00:00 UTC',
            'currencies' => ['US Dollar' => 'USD'],
        ]);

        CurrencyService::deleteUnits();
        CurrencyService::deleteUnits();

        $this->assertFileDoesNotExist(CurrencyService::getUnitsFilePath());
        $this->assertFileDoesNotExist(CurrencyService::getXmlFilePath());
        $this->assertSame(self::snapshotWhenFetched(), CurrencyService::getUnits()['whenFetched']);
    }

    #endregion

    #region Method init() tests.

    /**
     * Test init() sets the configuration without fetching anything.
     */
    public function testInitConfiguresWithoutFetching(): void
    {
        $service = new FakeExchangeRateService();
        CurrencyService::init($service, 'de_DE', 600);

        $this->assertSame($service, CurrencyService::getExchangeRateService());
        $this->assertSame('de_DE', CurrencyService::getLocale());
        $this->assertSame(600, CurrencyService::getAutoRefreshTtl());
        $this->assertSame(0, $service->fetchCount);
        $this->assertSame([], glob($this->dataDir . '/*'));
    }

    /**
     * Test init() leaves auto-refresh off by default.
     */
    public function testInitLeavesAutoRefreshOffByDefault(): void
    {
        CurrencyService::init(new FakeExchangeRateService());

        $this->assertNull(CurrencyService::getAutoRefreshTtl());
    }

    #endregion

    #region Method setAutoRefreshTtl() tests.

    /**
     * Test setAutoRefreshTtl() accepts zero and null.
     */
    public function testSetAutoRefreshTtlAcceptsZeroAndNull(): void
    {
        CurrencyService::setAutoRefreshTtl(0);
        $this->assertSame(0, CurrencyService::getAutoRefreshTtl());

        CurrencyService::setAutoRefreshTtl(null);
        $this->assertNull(CurrencyService::getAutoRefreshTtl());
    }

    /**
     * Test setAutoRefreshTtl() throws for a negative value.
     */
    public function testSetAutoRefreshTtlThrowsForNegativeValue(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid auto-refresh TTL: -1. Must be non-negative.');
        CurrencyService::setAutoRefreshTtl(-1);
    }

    #endregion

    #region Method setDataDir() tests.

    /**
     * Test setDataDir() changes the data directory and file paths, and strips a trailing slash.
     */
    public function testSetDataDirChangesPaths(): void
    {
        CurrencyService::setDataDir($this->dataDir . '/');

        $this->assertSame($this->dataDir, CurrencyService::getDataDir());
        $this->assertSame($this->dataDir . '/CurrencyUnits.php', CurrencyService::getUnitsFilePath());
        $this->assertSame($this->dataDir . '/CurrencyConversions.php', CurrencyService::getConversionsFilePath());
        $this->assertSame($this->dataDir . '/CurrencyUnits.xml', CurrencyService::getXmlFilePath());
    }

    /**
     * Test setDataDir() creates the directory if needed.
     */
    public function testSetDataDirCreatesDirectory(): void
    {
        rmdir($this->dataDir);

        CurrencyService::setDataDir($this->dataDir);

        $this->assertDirectoryExists($this->dataDir);
    }

    /**
     * Test setDataDir() throws for an empty path.
     */
    public function testSetDataDirThrowsForEmptyPath(): void
    {
        $this->expectException(DomainException::class);
        CurrencyService::setDataDir('');
    }

    /**
     * Test setDataDir() throws for a path that's only a slash.
     */
    public function testSetDataDirThrowsForSlashOnly(): void
    {
        $this->expectException(DomainException::class);
        CurrencyService::setDataDir('/');
    }

    #endregion

    #region Method resetDataDir() tests.

    /**
     * Test resetDataDir() restores the default data directory in the system temp directory.
     */
    public function testResetDataDirRestoresDefault(): void
    {
        CurrencyService::resetDataDir();

        $this->assertSame(sys_get_temp_dir() . '/mossy2100-quantities', CurrencyService::getDataDir());
    }

    #endregion

    #region Method setLocale() tests.

    /**
     * Test setLocale() sets the locale returned by getLocale().
     */
    public function testSetLocaleAcceptsVariousValidFormats(): void
    {
        foreach (['en', 'en_US', 'en_AU', 'zh-Hant-TW'] as $locale) {
            CurrencyService::setLocale($locale);
            $this->assertSame($locale, CurrencyService::getLocale());
        }
    }

    /**
     * Test setLocale(null) clears the locale, so getLocale() falls back to auto-detection.
     */
    public function testSetLocaleNullFallsBackToDefault(): void
    {
        CurrencyService::setLocale('en_AU');
        CurrencyService::setLocale(null);

        $locale = CurrencyService::getLocale();

        $this->assertNotNull($locale);
        $this->assertNotSame('', $locale);
    }

    /**
     * Test setLocale() throws for an invalid locale string.
     */
    public function testSetLocaleThrowsForInvalidLocale(): void
    {
        $this->expectException(FormatException::class);
        CurrencyService::setLocale('!!invalid');
    }

    #endregion

    #region Helper methods

    /**
     * Write a PHP data file returning the given data into the data directory.
     *
     * @param string $fileName The file name.
     * @param array<string, mixed> $data The data the file returns.
     */
    private function writeDataFile(string $fileName, array $data): void
    {
        file_put_contents($this->dataDir . '/' . $fileName, '<?php return ' . var_export($data, true) . ';');
    }

    /**
     * Get when the bundled snapshot was fetched, so tests don't depend on the snapshot's current contents.
     *
     * @return string The snapshot's whenFetched value.
     */
    private static function snapshotWhenFetched(): string
    {
        $snapshot = require __DIR__ . '/../../../src/Currencies/snapshot/CurrencyUnits.php';
        assert(is_array($snapshot) && is_string($snapshot['whenFetched']));
        return $snapshot['whenFetched'];
    }

    /**
     * Set the URL that refreshUnits() fetches the ISO 4217 XML from.
     *
     * @param string $url The URL or file path.
     */
    private static function setIso4217Url(string $url): void
    {
        new ReflectionProperty(CurrencyService::class, 'iso4217Url')->setValue(null, $url);
    }

    /**
     * Write an exchange rates file into the data directory.
     *
     * @param string $whenFetched When the rates were fetched.
     * @param string $serviceName The name of the service the rates came from.
     * @param list<list<string|float>> $definitions The conversion definitions.
     */
    private function writeConversionsFile(string $whenFetched, string $serviceName, array $definitions): void
    {
        $this->writeDataFile('CurrencyConversions.php', [
            'whenFetched' => $whenFetched,
            'serviceName' => $serviceName,
            'definitions' => $definitions,
        ]);
    }

    #endregion
}
