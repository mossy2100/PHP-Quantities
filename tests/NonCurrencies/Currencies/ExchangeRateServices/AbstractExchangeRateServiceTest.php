<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Currencies\ExchangeRateServices;

use Mossy2100\Quantities\Currencies\ExchangeRateServices\AbstractExchangeRateService;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Offline tests for AbstractExchangeRateService.
 *
 * Uses a minimal subclass that fetches from a data: URL, so the real fetchJson() runs without a network.
 */
#[CoversClass(AbstractExchangeRateService::class)]
class AbstractExchangeRateServiceTest extends TestCase
{
    #region Helper methods

    /**
     * Create a service that fetches from the given URL.
     *
     * The response's 'base' field is the base currency (default USD), its 'rates' field the rates, and its 'error'
     * field, if a string, the error message.
     *
     * @param string $url The URL to fetch from.
     * @return AbstractExchangeRateService The service.
     */
    private static function createService(string $url): AbstractExchangeRateService
    {
        return new class ($url) extends AbstractExchangeRateService {
            public function __construct(private readonly string $url)
            {
            }

            #[Override]
            public function getName(): string
            {
                return 'Test';
            }

            #[Override]
            protected function getUrl(): string
            {
                return $this->url;
            }

            #[Override]
            protected function getErrorMessage(array $data): ?string
            {
                return isset($data['error']) && is_string($data['error']) ? $data['error'] : null;
            }

            #[Override]
            protected function getRates(array $data): array
            {
                return [$this->getStringField($data, 'base', 'USD'), $this->getArrayField($data, 'rates')];
            }
        };
    }

    /**
     * Create a service whose response is the given JSON.
     *
     * @param string $json The response body.
     * @return AbstractExchangeRateService The service.
     */
    private static function createServiceWithJson(string $json): AbstractExchangeRateService
    {
        return self::createService('data:application/json,' . rawurlencode($json));
    }

    #endregion

    #region Method getConversionDefinitions() tests.

    /**
     * Test getConversionDefinitions() maps each rate to a definition from the base currency.
     */
    public function testGetConversionDefinitionsMapsRates(): void
    {
        $service = self::createServiceWithJson('{"base": "EUR", "rates": {"USD": 1.1, "GBP": 0.85}}');

        $this->assertSame([['EUR', 'USD', 1.1], ['EUR', 'GBP', 0.85]], $service->getConversionDefinitions());
    }

    /**
     * Test getConversionDefinitions() throws when the response is an error.
     */
    public function testGetConversionDefinitionsThrowsForErrorResponse(): void
    {
        $service = self::createServiceWithJson('{"error": "Invalid key."}');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Test returned an error: Invalid key.');
        $service->getConversionDefinitions();
    }

    #endregion

    #region Method fetchJson() tests.

    /**
     * Test fetchJson() throws when the request fails.
     */
    public function testFetchJsonThrowsWhenRequestFails(): void
    {
        $service = self::createService(__DIR__ . '/no-such-file.json');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to fetch exchange rates from Test.');
        $service->getConversionDefinitions();
    }

    /**
     * Test fetchJson() throws when the response isn't JSON.
     */
    public function testFetchJsonThrowsForInvalidJson(): void
    {
        $service = self::createServiceWithJson('not json');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON response from Test.');
        $service->getConversionDefinitions();
    }

    /**
     * Test fetchJson() throws when the response is JSON but not an object or array.
     */
    public function testFetchJsonThrowsForJsonScalar(): void
    {
        $service = self::createServiceWithJson('5');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON response from Test.');
        $service->getConversionDefinitions();
    }

    #endregion

    #region Method getErrorMessage() tests.

    /**
     * Test the default getErrorMessage() treats every response as a success.
     */
    public function testGetErrorMessageDefaultReturnsNull(): void
    {
        $service = new class extends AbstractExchangeRateService {
            #[Override]
            public function getName(): string
            {
                return 'Test';
            }

            #[Override]
            protected function getUrl(): string
            {
                return 'data:application/json,' . rawurlencode('{"success": false, "rates": {"EUR": 0.9}}');
            }

            #[Override]
            protected function getRates(array $data): array
            {
                return ['USD', $this->getArrayField($data, 'rates')];
            }
        };

        $this->assertSame([['USD', 'EUR', 0.9]], $service->getConversionDefinitions());
    }

    #endregion

    #region Method getArrayField() tests.

    /**
     * Test getArrayField() throws when the field is missing.
     */
    public function testGetArrayFieldThrowsWhenMissing(): void
    {
        $service = self::createServiceWithJson('{"base": "USD"}');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing or invalid rates in Test response.');
        $service->getConversionDefinitions();
    }

    /**
     * Test getArrayField() throws when the field isn't an array.
     */
    public function testGetArrayFieldThrowsWhenNotArray(): void
    {
        $service = self::createServiceWithJson('{"rates": "none"}');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing or invalid rates in Test response.');
        $service->getConversionDefinitions();
    }

    #endregion

    #region Method getStringField() tests.

    /**
     * Test getStringField() returns the default when the field is missing or isn't a string.
     */
    public function testGetStringFieldReturnsDefault(): void
    {
        $missing = self::createServiceWithJson('{"rates": {"EUR": 0.9}}');
        $notString = self::createServiceWithJson('{"base": 5, "rates": {"EUR": 0.9}}');

        $this->assertSame([['USD', 'EUR', 0.9]], $missing->getConversionDefinitions());
        $this->assertSame([['USD', 'EUR', 0.9]], $notString->getConversionDefinitions());
    }

    #endregion

    #region Method buildDefinitions() tests.

    /**
     * Test buildDefinitions() skips the base currency.
     */
    public function testBuildDefinitionsSkipsBaseCurrency(): void
    {
        $service = self::createServiceWithJson('{"base": "USD", "rates": {"USD": 1, "EUR": 0.9}}');

        $this->assertSame([['USD', 'EUR', 0.9]], $service->getConversionDefinitions());
    }

    /**
     * Test buildDefinitions() casts a whole-number rate to float.
     */
    public function testBuildDefinitionsCastsIntRateToFloat(): void
    {
        $service = self::createServiceWithJson('{"rates": {"JPY": 150}}');

        $this->assertSame([['USD', 'JPY', 150.0]], $service->getConversionDefinitions());
    }

    /**
     * Test buildDefinitions() skips rates that aren't positive numbers.
     */
    public function testBuildDefinitionsSkipsInvalidRates(): void
    {
        $service = self::createServiceWithJson(
            '{"rates": {"AAA": 0, "BBB": -1.5, "CCC": "1.2", "DDD": null, "EEE": [], "EUR": 0.9}}'
        );

        $this->assertSame([['USD', 'EUR', 0.9]], $service->getConversionDefinitions());
    }

    #endregion
}
