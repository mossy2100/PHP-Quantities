<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Currencies;

use DateTimeImmutable;
use DateTimeInterface;
use DomainException;
use Locale;
use LogicException;
use Mossy2100\Core\Exceptions\FormatException;
use Mossy2100\Core\Numbers;
use Mossy2100\Core\Stringify;
use Mossy2100\Core\StringifyOptions;
use Mossy2100\Quantities\Currencies\ExchangeRateServices\ExchangeRateServiceInterface;
use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Internal\Converter;
use Mossy2100\Quantities\Services\ConversionService;
use Mossy2100\Quantities\Services\UnitService;
use ParseError;
use RuntimeException;
use SimpleXMLElement;

/**
 * Service for managing currency units and exchange rate conversions.
 *
 * Currencies always work from local data, and nothing is fetched unless asked for:
 * - Currency units come from the data directory if refreshUnits() has been called, or otherwise from a snapshot of
 *   the ISO 4217 list bundled with the package.
 * - Exchange rates come from the data directory, once refreshRates() has been called. Until then, currency
 *   conversions fail like any other conversion with no known path.
 *
 * If auto-refresh is enabled (see setAutoRefreshTtl()), a currency conversion refetches the rates first whenever they
 * are stale.
 */
class CurrencyService
{
    #region Private constants

    /**
     * The path of the ISO 4217 snapshot bundled with the package, used when no refreshed copy exists.
     */
    private const string SNAPSHOT_UNITS_PATH = __DIR__ . '/snapshot/CurrencyUnits.php';

    /**
     * The URL for the official ISO 4217 XML published by SIX Group.
     *
     * Note: "currrency" with three r's is SIX Group's actual URL, not a typo in our code.
     */
    private const string ISO_4217_URL =
        'https://www.six-group.com/dam/download/financial-information/data-center/iso-currrency/lists/list-one.xml';

    /**
     * The regular expression for matching a locale string.
     */
    private const string RX_LOCALE = '[a-z]{2,4}([-_][A-Z][a-z]{3})?([-_]([A-Z]{2}|[0-9]{3}))?';

    /**
     * The date format used for timestamps in generated data files. Always UTC, written with gmdate(), since time
     * zone abbreviations are ambiguous when parsed back.
     */
    private const string DATETIME_FORMAT = 'Y-m-d H:i:s \\U\\T\\C';

    #endregion

    #region Private static properties

    /**
     * The exchange rate service.
     */
    private static ?ExchangeRateServiceInterface $exchangeRateService = null;

    /**
     * The locale used for currency formatting.
     */
    private static ?string $locale = null;

    /**
     * The maximum age of the exchange rates in seconds before a currency conversion refetches them, or null if
     * auto-refresh is off.
     */
    private static ?int $autoRefreshTtl = null;

    /**
     * The directory where refreshed currency data files are stored, or null for the default.
     */
    private static ?string $dataDir = null;

    /**
     * Whether the exchange rate metadata below has been read from the data directory.
     */
    private static bool $ratesStateLoaded = false;

    /**
     * When the local exchange rates were fetched, as a Unix timestamp, or null if there are none.
     */
    private static ?int $ratesFetchedAt = null;

    /**
     * The name of the service the local exchange rates were fetched from, or null if there are none.
     */
    private static ?string $ratesServiceName = null;

    #endregion

    #region Unit data methods

    /**
     * Get the currency unit data.
     *
     * Returns the refreshed copy in the data directory if there is one, or otherwise the ISO 4217 snapshot bundled
     * with the package. Never fetches anything.
     *
     * @return array{
     *     whenFetched: string,
     *     currencies: array<string, string>
     * } The currency unit data.
     * @throws RuntimeException If there is no refreshed copy and the bundled snapshot is missing or invalid.
     */
    public static function getUnits(): array
    {
        $unitData = self::loadUnits(self::getUnitsFilePath()) ?? self::loadUnits(self::SNAPSHOT_UNITS_PATH);

        // The snapshot is bundled with the package, so this should never happen.
        if ($unitData === null) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('The bundled currency snapshot is missing or invalid.');
            // @codeCoverageIgnoreEnd
        }

        return $unitData;
    }

    /**
     * Fetch the currencies from an official source (SIX Group) and write them to the data directory.
     *
     * Downloads the ISO 4217 XML, filters out fund currencies and excluded codes, renames SDR (XDR), writes the
     * generated PHP data file, and reloads the currency units and conversions.
     *
     * The currency list changes rarely, so this only needs calling occasionally.
     *
     * @return array{
     *     whenFetched: string,
     *     currencies: array<string, string>
     * } The currency unit data just written.
     * @throws RuntimeException If the ISO 4217 XML cannot be fetched or parsed, or a data file cannot be written.
     */
    public static function refreshUnits(): array
    {
        // Fetch the official ISO 4217 XML.
        $xmlContent = @file_get_contents(self::ISO_4217_URL);
        if ($xmlContent === false) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException("Failed to fetch ISO 4217 XML from '" . self::ISO_4217_URL . "'.");
            // @codeCoverageIgnoreEnd
        }

        // Save the XML for reference, it's useful for debugging.
        self::writeFile(self::getXmlFilePath(), $xmlContent);

        // Convert to SimpleXML.
        $xml = @simplexml_load_string($xmlContent);
        if (!$xml instanceof SimpleXMLElement) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Failed to parse ISO 4217 XML.');
            // @codeCoverageIgnoreEnd
        }

        $currencies = [];

        // Parse currencies from the XML.
        // Silence PHPCS temporarily here because of the properties that aren't in lowerCamelCase.
        // phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
        foreach ($xml->CcyTbl->CcyNtry as $entry) {
            $code = (string) $entry->Ccy;
            $name = trim((string) $entry->CcyNm);
            $isFund = isset($entry->CcyNm['IsFund']) && (string) $entry->CcyNm['IsFund'] === 'true';
            // phpcs:enable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

            // Skip currencies we don't need.
            if ($isFund || str_starts_with($code, 'XB') || $code === '' || $code === 'XTS' || $code === 'XXX') {
                continue;
            }

            // Rename SDR so the generated data doesn't carry the parenthesized form from ISO 4217.
            if ($code === 'XDR') {
                $name = 'Special Drawing Right';
            }

            // Skip if already added (multiple countries can share a currency).
            if (isset($currencies[$name])) {
                continue;
            }

            $currencies[$name] = $code;
        }

        // Construct the data array.
        ksort($currencies);
        $now = time();
        $unitData = ['whenFetched' => gmdate(self::DATETIME_FORMAT, $now), 'currencies' => $currencies];

        // Save it.
        self::writeFile(self::getUnitsFilePath(), self::buildUnitsFile($unitData, $now));

        // Refresh the currency units.
        UnitService::loadSystem(UnitSystem::Financial, true);

        // Load any missing conversions. If new currency codes have been added, some conversion definitions not
        // loaded before, due to unknown units, may now be loadable.
        Converter::getInstance('C')->loadConversions();

        return $unitData;
    }

    /**
     * Delete the refreshed currency unit data files from the data directory.
     *
     * The bundled snapshot is used again after this.
     */
    public static function deleteUnits(): void
    {
        self::deleteFile(self::getXmlFilePath());
        self::deleteFile(self::getUnitsFilePath());
    }

    #endregion

    #region Conversion data methods

    /**
     * Get the local exchange rate data.
     *
     * Returns the rates last fetched by refreshRates(), or null if there are none. Never fetches anything.
     *
     * @return ?array{
     *     whenFetched: string,
     *     serviceName: string,
     *     definitions: list<array{string, string, float}>
     * } The currency conversion data, or null if there is none.
     */
    public static function getConversions(): ?array
    {
        $conversionData = self::loadConversions();

        // Remember when and where the rates came from, so staleness checks don't need to read the file.
        $fetchedAt = $conversionData === null ? false : strtotime($conversionData['whenFetched']);
        self::$ratesFetchedAt = $fetchedAt === false ? null : $fetchedAt;
        self::$ratesServiceName = $conversionData['serviceName'] ?? null;
        self::$ratesStateLoaded = true;

        return $conversionData;
    }

    /**
     * Fetch exchange rates from the configured service and write them to the data directory.
     *
     * Writes the generated PHP data file, purges existing currency conversions, and reloads them.
     *
     * @return array{
     *     whenFetched: string,
     *     serviceName: string,
     *     definitions: list<array{string, string, float}>
     * } The conversion data just written.
     * @throws LogicException If the exchange rate service is not configured.
     * @throws RuntimeException If the exchange rate service fails to return valid data, or the data file cannot be
     * written.
     */
    public static function refreshRates(): array
    {
        // Get the latest exchange rates.
        $exchangeRateService = self::getConfiguredExchangeRateService();
        $conversionDefinitions = $exchangeRateService->getConversionDefinitions();
        $serviceName = $exchangeRateService->getName();

        // Construct the data array.
        $now = time();
        $conversionData = [
            'whenFetched' => gmdate(self::DATETIME_FORMAT, $now),
            'serviceName' => $serviceName,
            'definitions' => $conversionDefinitions,
        ];

        // Build the PHP file content.
        $datetime = gmdate(DateTimeInterface::COOKIE, $now);
        $className = self::class;
        $output = <<<PHP
            <?php

            /**
             * Conversion definitions for currencies.
             *
             * Fetched from $serviceName at $datetime.
             *
             * To regenerate, call $className::refreshRates().
             *
             * @return array{
             *     whenFetched: string,
             *     serviceName: string,
             *     definitions: list<array{string, string, float}>
             * }
             */

            declare(strict_types=1);
            PHP;
        $output .= "\n\nreturn " . Stringify::stringify($conversionData, StringifyOptions::pretty()) . ";\n";

        // Save it.
        self::writeFile(self::getConversionsFilePath(), $output);

        // Remember when and where the rates came from.
        self::$ratesFetchedAt = $now;
        self::$ratesServiceName = $serviceName;
        self::$ratesStateLoaded = true;

        // Remove all conversions involving currencies. We can't just overwrite the conversions between currencies
        // because conversions involving currencies may have been created for dimensions other than 'C'.
        ConversionService::removeBySystem(UnitSystem::Financial);

        // Reload currency conversions.
        Converter::getInstance('C')->loadConversions(true);

        return $conversionData;
    }

    /**
     * Refetch the exchange rates if auto-refresh is on and the local rates are stale.
     *
     * The rates are stale if there are none, if they're older than the auto-refresh TTL, or if they came from a
     * different service than the one configured. Does nothing if auto-refresh is off.
     *
     * Called before each currency conversion. The check uses metadata held in memory, so it's cheap.
     *
     * @throws LogicException If auto-refresh is on but the exchange rate service is not configured.
     * @throws RuntimeException If a refetch is needed and fails.
     * @internal
     */
    public static function refreshRatesIfStale(): void
    {
        // Check if auto-refresh is on.
        if (self::$autoRefreshTtl === null) {
            return;
        }

        // Make sure we know when and where the local rates came from.
        if (!self::$ratesStateLoaded) {
            self::getConversions();
        }

        // Refetch if the rates are missing, too old, or from a different service.
        $serviceName = self::getConfiguredExchangeRateService()->getName();
        if (
            self::$ratesFetchedAt === null ||
            time() > self::$ratesFetchedAt + self::$autoRefreshTtl ||
            self::$ratesServiceName !== $serviceName
        ) {
            self::refreshRates();
        }
    }

    /**
     * Get when the local exchange rates were fetched.
     *
     * Useful for deciding whether to call refreshRates() when auto-refresh is off.
     *
     * @return ?DateTimeImmutable The time the rates were fetched, or null if there are no local rates.
     */
    public static function getRatesFetchedAt(): ?DateTimeImmutable
    {
        if (!self::$ratesStateLoaded) {
            self::getConversions();
        }

        return self::$ratesFetchedAt === null ? null : new DateTimeImmutable('@' . self::$ratesFetchedAt);
    }

    /**
     * Delete the exchange rate data file from the data directory.
     */
    public static function deleteConversions(): void
    {
        self::deleteFile(self::getConversionsFilePath());
        self::$ratesStateLoaded = false;
    }

    #endregion

    #region Main methods

    /**
     * Initialize the currency service.
     *
     * This only sets the configuration. Nothing is fetched until refreshRates() or refreshUnits() is called, or a
     * currency conversion runs with auto-refresh on.
     *
     * @param ExchangeRateServiceInterface $exchangeRateService The exchange rate service.
     * @param ?string $locale The locale used for currency formatting.
     * @param ?int $autoRefreshTtl The maximum age of the rates in seconds before a currency conversion refetches
     * them, or null to turn auto-refresh off (default).
     * @throws FormatException If the locale string is invalid.
     * @throws DomainException If the auto-refresh TTL is negative.
     */
    public static function init(
        ExchangeRateServiceInterface $exchangeRateService,
        ?string $locale = null,
        ?int $autoRefreshTtl = null
    ): void {
        self::setExchangeRateService($exchangeRateService);
        self::setLocale($locale);
        self::setAutoRefreshTtl($autoRefreshTtl);
    }

    #endregion

    #region Configuration

    /**
     * Get the exchange rate service.
     *
     * @return ?ExchangeRateServiceInterface The exchange rate service, or null if not configured.
     */
    public static function getExchangeRateService(): ?ExchangeRateServiceInterface
    {
        return self::$exchangeRateService;
    }

    /**
     * Set the exchange rate service.
     *
     * @param ?ExchangeRateServiceInterface $exchangeRateService The exchange rate service, or null to clear.
     */
    public static function setExchangeRateService(?ExchangeRateServiceInterface $exchangeRateService): void
    {
        self::$exchangeRateService = $exchangeRateService;
    }

    /**
     * Get the locale for currency formatting.
     *
     * Returns the explicitly set locale, or auto-detects from the HTTP Accept-Language header, falling back to PHP's
     * default locale.
     *
     * If a locale is successfully determined, it is cached in the static $locale property for subsequent calls.
     *
     * @return ?string The locale string, or null if none could be determined.
     */
    public static function getLocale(): ?string
    {
        // Return if already set.
        if (self::$locale !== null) {
            return self::$locale;
        }

        // Try to detect from the HTTP Accept-Language header.
        // @codeCoverageIgnoreStart
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && is_string($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $detected = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if ($detected !== false) {
                self::$locale = $detected;
                return self::$locale;
            }
        }
        // @codeCoverageIgnoreEnd

        // Fall back to PHP's default locale.
        $default = Locale::getDefault();
        if (!empty($default)) {
            self::$locale = $default;
        }

        return self::$locale;
    }

    /**
     * Set the locale used for currency formatting.
     *
     * Pass null to clear an explicitly set locale and revert to auto-detection.
     *
     * @param ?string $locale A BCP 47 / ICU locale string (e.g. 'en_US', 'de_DE'), or null.
     * @throws FormatException If the locale string is invalid.
     */
    public static function setLocale(?string $locale): void
    {
        if ($locale !== null && !preg_match('/^' . self::RX_LOCALE . '$/', $locale)) {
            throw new FormatException("Invalid locale string: '$locale'.");
        }
        self::$locale = $locale;
    }

    /**
     * Get the auto-refresh TTL for exchange rates.
     *
     * @return ?int The maximum age of the rates in seconds, or null if auto-refresh is off.
     */
    public static function getAutoRefreshTtl(): ?int
    {
        return self::$autoRefreshTtl;
    }

    /**
     * Set the auto-refresh TTL for exchange rates.
     *
     * When set, a currency conversion first refetches the rates if they're older than this, missing, or from a
     * different service than the one configured. When null (the default), rates are only fetched by refreshRates().
     *
     * @param ?int $autoRefreshTtl The maximum age of the rates in seconds, or null to turn auto-refresh off.
     * @throws DomainException If the value is negative.
     */
    public static function setAutoRefreshTtl(?int $autoRefreshTtl): void
    {
        if ($autoRefreshTtl !== null && $autoRefreshTtl < 0) {
            throw new DomainException("Invalid auto-refresh TTL: $autoRefreshTtl. Must be non-negative.");
        }
        self::$autoRefreshTtl = $autoRefreshTtl;
    }

    /**
     * Get the current data directory path.
     *
     * The default is a directory in the system temp directory. Since temp directories can be cleared, it's best to
     * call setDataDir() in production.
     *
     * @return string The directory path.
     */
    public static function getDataDir(): string
    {
        return self::$dataDir ?? sys_get_temp_dir() . '/mossy2100-quantities';
    }

    /**
     * Set the data directory for refreshed currency data files.
     *
     * @param string $dataDir The directory path.
     * @throws DomainException If the path is empty.
     * @throws RuntimeException If the directory cannot be created.
     */
    public static function setDataDir(string $dataDir): void
    {
        $dataDir = rtrim($dataDir, '/');
        if ($dataDir === '') {
            throw new DomainException('Data directory path cannot be empty.');
        }
        self::ensureDirExists($dataDir);
        self::$dataDir = $dataDir;
        self::$ratesStateLoaded = false;
    }

    /**
     * Reset the data directory for refreshed currency data files to the default (a directory in the system temp
     * directory).
     */
    public static function resetDataDir(): void
    {
        self::$dataDir = null;
        self::$ratesStateLoaded = false;
    }

    /**
     * Get the path to the currency units data file.
     *
     * @return string The file path.
     */
    public static function getUnitsFilePath(): string
    {
        return self::getDataDir() . '/CurrencyUnits.php';
    }

    /**
     * Get the path to the currency conversions data file.
     *
     * @return string The file path.
     */
    public static function getConversionsFilePath(): string
    {
        return self::getDataDir() . '/CurrencyConversions.php';
    }

    /**
     * Get the path to the downloaded currencies XML file.
     *
     * @return string The file path.
     * @internal
     */
    public static function getXmlFilePath(): string
    {
        return self::getDataDir() . '/CurrencyUnits.xml';
    }

    #endregion

    #region Helper methods

    /**
     * Load currency unit data from a generated PHP file, if it exists.
     *
     * Returns null if the file is missing, contains a syntax error, or doesn't return an array with the expected
     * shape. No exceptions are raised. Any failure condition yields a null result.
     *
     * @param string $path The path of the file to load.
     * @return ?array{
     *     whenFetched: string,
     *     currencies: array<string, string>
     * } The currency unit data, or null if no valid data is available.
     */
    private static function loadUnits(string $path): ?array
    {
        // Check the file exists.
        if (!file_exists($path)) {
            return null;
        }

        // Try to load the data.
        try {
            $unitData = require $path;
        } catch (ParseError) {
            return null;
        }

        // Check it's an array with the right shape.
        $ok = is_array($unitData)
            && array_key_exists('whenFetched', $unitData) && is_string($unitData['whenFetched'])
            && array_key_exists('currencies', $unitData) && is_array($unitData['currencies']);

        // Check currencies are arrays with the right shape.
        if ($ok) {
            foreach ($unitData['currencies'] as $name => $code) {
                $ok = $ok && is_string($name) && is_string($code);
            }
        }

        return $ok ? $unitData : null;
    }

    /**
     * Load the exchange rate data from the generated PHP file in the data directory, if it exists.
     *
     * Returns null if the file is missing, contains a syntax error, or doesn't return an array with the expected
     * shape. No exceptions are raised. Any failure condition yields a null result.
     *
     * @return ?array{
     *     whenFetched: string,
     *     serviceName: string,
     *     definitions: list<array{string, string, float}>
     * } The currency conversion data, or null if no valid data is available.
     */
    private static function loadConversions(): ?array
    {
        // Check the file exists.
        $path = self::getConversionsFilePath();
        if (!file_exists($path)) {
            return null;
        }

        // Try to load the data.
        try {
            $conversionData = require $path;
        } catch (ParseError) {
            return null;
        }

        // Check it's an array with the right shape.
        $ok = is_array($conversionData)
            && array_key_exists('whenFetched', $conversionData) && is_string($conversionData['whenFetched'])
            && array_key_exists('serviceName', $conversionData) && is_string($conversionData['serviceName'])
            && array_key_exists('definitions', $conversionData) && is_array($conversionData['definitions']);

        if (!$ok) {
            return null;
        }

        // Check definitions are arrays with the right shape. A whole-number rate may have been written as an int, so
        // accept that too, and cast it to float.
        $definitions = [];
        foreach ($conversionData['definitions'] as $definition) {
            if (
                !is_array($definition) || count($definition) !== 3 || !is_string($definition[0]) ||
                !is_string($definition[1]) || !Numbers::isNumber($definition[2])
            ) {
                return null;
            }
            $definitions[] = [$definition[0], $definition[1], (float) $definition[2]];
        }

        return [
            'whenFetched' => $conversionData['whenFetched'],
            'serviceName' => $conversionData['serviceName'],
            'definitions' => $definitions,
        ];
    }

    /**
     * Build the content of a generated currency units PHP file.
     *
     * @param array{
     *     whenFetched: string,
     *     currencies: array<string, string>
     * } $unitData The currency unit data.
     * @param int $fetchedAt When the data was fetched, as a Unix timestamp.
     * @return string The file content.
     */
    private static function buildUnitsFile(array $unitData, int $fetchedAt): string
    {
        $url = self::ISO_4217_URL;
        $datetime = gmdate(DateTimeInterface::COOKIE, $fetchedAt);
        $className = self::class;
        $output = <<<PHP
            <?php

            /**
             * Currencies, based off ISO 4217 currency data.
             *
             * Fetched from
             * $url
             * at $datetime.
             *
             * Auto-generated from the official ISO 4217 XML published by SIX Group.
             *
             * To regenerate, call $className::refreshUnits().
             *
             * @return array{
             *     whenFetched: string,
             *     currencies: array<string, string>
             * }
             */

            declare(strict_types=1);
            PHP;
        return $output . "\n\nreturn " . Stringify::stringify($unitData, StringifyOptions::pretty()) . ";\n";
    }

    /**
     * Get the exchange rate service, ensuring that it's configured.
     *
     * @return ExchangeRateServiceInterface The exchange rate service.
     * @throws LogicException If the exchange rate service is not configured.
     */
    private static function getConfiguredExchangeRateService(): ExchangeRateServiceInterface
    {
        if (self::$exchangeRateService === null) {
            throw new LogicException('The exchange rate service is not configured.');
        }

        return self::$exchangeRateService;
    }

    /**
     * Write a file atomically, creating its directory if necessary.
     *
     * The content is written to a temporary file and then renamed into place, so a concurrent reader never sees a
     * partially written file.
     *
     * @param string $path The file path.
     * @param string $content The file content.
     * @throws RuntimeException If the directory cannot be created or the file cannot be written.
     */
    private static function writeFile(string $path, string $content): void
    {
        self::ensureDirExists(dirname($path));

        $tempPath = $path . '.' . bin2hex(random_bytes(4)) . '.tmp';
        if (@file_put_contents($tempPath, $content) === false) {
            throw new RuntimeException("Failed to write file: '$path'.");
        }
        if (!@rename($tempPath, $path)) {
            // @codeCoverageIgnoreStart
            @unlink($tempPath);
            throw new RuntimeException("Failed to write file: '$path'.");
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Ensure that a directory exists, creating it if necessary.
     *
     * @param string $dirPath The directory path.
     * @throws RuntimeException If the directory cannot be created.
     */
    private static function ensureDirExists(string $dirPath): void
    {
        if (!is_dir($dirPath) && !@mkdir($dirPath, 0755, true)) {
            throw new RuntimeException("Failed to create directory: '$dirPath'.");
        }
    }

    /**
     * Delete a file if it exists.
     *
     * @param string $path The file path.
     */
    private static function deleteFile(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    #endregion
}
