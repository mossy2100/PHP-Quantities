<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Currencies\ExchangeRateServices;

use DomainException;
use Mossy2100\Quantities\Currencies\ExchangeRateServices\OpenExchangeRatesService;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;

/**
 * Offline tests for OpenExchangeRatesService, using canned API responses.
 *
 * The live API is tested in tests/Currencies/ExchangeRateServices/OpenExchangeRatesServiceTest.php.
 */
#[CoversClass(OpenExchangeRatesService::class)]
class OpenExchangeRatesServiceTest extends TestCase
{
    #region Helper methods

    /**
     * Create a service that returns the given response instead of calling the API.
     *
     * @param array<array-key, mixed> $response The decoded API response.
     * @return OpenExchangeRatesService The service.
     */
    private static function createService(array $response): OpenExchangeRatesService
    {
        return new class ($response) extends OpenExchangeRatesService {
            /**
             * @param array<array-key, mixed> $response The decoded API response.
             */
            public function __construct(private readonly array $response)
            {
                parent::__construct('test-id');
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
     * Test the constructor rejects an empty App ID.
     */
    public function testConstructorRejectsEmptyAppId(): void
    {
        $this->expectException(DomainException::class);
        new OpenExchangeRatesService('');
    }

    /**
     * Test the constructor stores the App ID.
     */
    public function testConstructorStoresAppId(): void
    {
        $service = new OpenExchangeRatesService('test-app-id');
        $this->assertSame('test-app-id', $service->appId);
    }

    #endregion

    #region Method getName() tests.

    /**
     * Test getName() returns the service name.
     */
    public function testGetName(): void
    {
        $service = new OpenExchangeRatesService('test-app-id');
        $this->assertSame('Open Exchange Rates', $service->getName());
    }

    #endregion

    #region Method getUrl() tests.

    /**
     * Test getUrl() includes the URL-encoded App ID.
     */
    public function testGetUrl(): void
    {
        $service = new OpenExchangeRatesService('id&1');

        $this->assertSame(
            'https://openexchangerates.org/api/latest.json?app_id=id%261',
            new ReflectionMethod($service, 'getUrl')->invoke($service)
        );
    }

    #endregion

    #region Method getErrorMessage() tests.

    /**
     * Test getConversionDefinitions() throws with the API's error message.
     */
    public function testGetErrorMessageUsesMessage(): void
    {
        $service = self::createService(['error' => true, 'status' => 401, 'message' => 'invalid_app_id']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Open Exchange Rates returned an error: invalid_app_id');
        $service->getConversionDefinitions();
    }

    /**
     * Test getConversionDefinitions() throws with a generic message when the error has no message.
     */
    public function testGetErrorMessageWithoutMessage(): void
    {
        $service = self::createService(['error' => true, 'status' => 401]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Open Exchange Rates returned an error: Unknown error');
        $service->getConversionDefinitions();
    }

    #endregion

    #region Method getRates() tests.

    /**
     * Test getConversionDefinitions() maps the rates from USD, skipping the base.
     */
    public function testGetRatesMapsRatesFromUsd(): void
    {
        $service = self::createService(['base' => 'USD', 'rates' => ['USD' => 1, 'EUR' => 0.9, 'JPY' => 150]]);

        $this->assertSame([['USD', 'EUR', 0.9], ['USD', 'JPY', 150.0]], $service->getConversionDefinitions());
    }

    /**
     * Test getConversionDefinitions() throws when the rates are missing.
     */
    public function testGetRatesThrowsWhenRatesMissing(): void
    {
        $service = self::createService(['base' => 'USD']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing or invalid rates in Open Exchange Rates response.');
        $service->getConversionDefinitions();
    }

    #endregion
}
