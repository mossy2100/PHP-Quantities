<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Currencies\ExchangeRateServices;

use DomainException;
use Mossy2100\Quantities\Currencies\ExchangeRateServices\ExchangeRateApiService;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;

/**
 * Offline tests for ExchangeRateApiService, using canned API responses.
 *
 * The live API is tested in tests/Currencies/ExchangeRateServices/ExchangeRateApiServiceTest.php.
 */
#[CoversClass(ExchangeRateApiService::class)]
class ExchangeRateApiServiceTest extends TestCase
{
    #region Helper methods

    /**
     * Create a service that returns the given response instead of calling the API.
     *
     * @param array<array-key, mixed> $response The decoded API response.
     * @return ExchangeRateApiService The service.
     */
    private static function createService(array $response): ExchangeRateApiService
    {
        return new class ($response) extends ExchangeRateApiService {
            /**
             * @param array<array-key, mixed> $response The decoded API response.
             */
            public function __construct(private readonly array $response)
            {
                parent::__construct('test-key');
            }

            #[Override]
            protected function fetchJson(string $url): array
            {
                return $this->response;
            }
        };
    }

    #endregion

    #region Method __construct() tests.

    /**
     * Test the constructor rejects an empty API key.
     */
    public function testConstructorRejectsEmptyApiKey(): void
    {
        $this->expectException(DomainException::class);
        new ExchangeRateApiService('');
    }

    /**
     * Test the constructor stores the API key.
     */
    public function testConstructorStoresApiKey(): void
    {
        $service = new ExchangeRateApiService('test-key');
        $this->assertSame('test-key', $service->apiKey);
    }

    #endregion

    #region Method getName() tests.

    /**
     * Test getName() returns the service name.
     */
    public function testGetName(): void
    {
        $service = new ExchangeRateApiService('test-key');
        $this->assertSame('ExchangeRate-API', $service->getName());
    }

    #endregion

    #region Method getUrl() tests.

    /**
     * Test getUrl() includes the URL-encoded API key and the USD base.
     */
    public function testGetUrl(): void
    {
        $service = new ExchangeRateApiService('key/1');

        $this->assertSame(
            'https://v6.exchangerate-api.com/v6/key%2F1/latest/USD',
            new ReflectionMethod($service, 'getUrl')->invoke($service)
        );
    }

    #endregion

    #region Method getErrorMessage() tests.

    /**
     * Test getConversionDefinitions() throws with the API's error type.
     */
    public function testGetErrorMessageUsesErrorType(): void
    {
        $service = self::createService(['result' => 'error', 'error-type' => 'invalid-key']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ExchangeRate-API returned an error: invalid-key');
        $service->getConversionDefinitions();
    }

    /**
     * Test getConversionDefinitions() throws with a generic message when the error has no type.
     */
    public function testGetErrorMessageWithoutErrorType(): void
    {
        $service = self::createService(['result' => 'error']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ExchangeRate-API returned an error: Unknown error');
        $service->getConversionDefinitions();
    }

    #endregion

    #region Method getRates() tests.

    /**
     * Test getConversionDefinitions() maps the rates from the base currency, skipping the base.
     */
    public function testGetRatesMapsRates(): void
    {
        $service = self::createService(
            ['result' => 'success', 'base_code' => 'USD', 'conversion_rates' => ['USD' => 1, 'EUR' => 0.9]]
        );

        $this->assertSame([['USD', 'EUR', 0.9]], $service->getConversionDefinitions());
    }

    /**
     * Test getConversionDefinitions() uses USD when the response has no base code.
     */
    public function testGetRatesDefaultsBaseToUsd(): void
    {
        $service = self::createService(['conversion_rates' => ['EUR' => 0.9]]);

        $this->assertSame([['USD', 'EUR', 0.9]], $service->getConversionDefinitions());
    }

    /**
     * Test getConversionDefinitions() throws when the rates are missing.
     */
    public function testGetRatesThrowsWhenRatesMissing(): void
    {
        $service = self::createService(['result' => 'success']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing or invalid conversion_rates in ExchangeRate-API response.');
        $service->getConversionDefinitions();
    }

    #endregion
}
