<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Currencies\ExchangeRateServices;

use DomainException;
use Mossy2100\Quantities\Currencies\ExchangeRateServices\CurrencyLayerService;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;

/**
 * Offline tests for CurrencyLayerService, using canned API responses.
 *
 * The live API is tested in tests/Currencies/ExchangeRateServices/CurrencyLayerServiceTest.php.
 */
#[CoversClass(CurrencyLayerService::class)]
class CurrencyLayerServiceTest extends TestCase
{
    #region Helper methods

    /**
     * Create a service that returns the given response instead of calling the API.
     *
     * @param array<array-key, mixed> $response The decoded API response.
     * @return CurrencyLayerService The service.
     */
    private static function createService(array $response): CurrencyLayerService
    {
        return new class ($response) extends CurrencyLayerService {
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
     * Test the constructor rejects an empty access key.
     */
    public function testConstructorRejectsEmptyAccessKey(): void
    {
        $this->expectException(DomainException::class);
        new CurrencyLayerService('');
    }

    /**
     * Test the constructor stores the access key.
     */
    public function testConstructorStoresAccessKey(): void
    {
        $service = new CurrencyLayerService('test-key');
        $this->assertSame('test-key', $service->accessKey);
    }

    #endregion

    #region Method getName() tests.

    /**
     * Test getName() returns the service name.
     */
    public function testGetName(): void
    {
        $service = new CurrencyLayerService('test-key');
        $this->assertSame('CurrencyLayer', $service->getName());
    }

    #endregion

    #region Method getUrl() tests.

    /**
     * Test getUrl() includes the URL-encoded access key.
     */
    public function testGetUrl(): void
    {
        $service = new CurrencyLayerService('key&1');

        $this->assertSame(
            'https://api.currencylayer.com/live?access_key=key%261',
            new ReflectionMethod($service, 'getUrl')->invoke($service)
        );
    }

    #endregion

    #region Method getErrorMessage() tests.

    /**
     * Test getConversionDefinitions() throws with the API's error info.
     */
    public function testGetErrorMessageUsesInfo(): void
    {
        $service = self::createService(
            ['success' => false, 'error' => ['code' => 101, 'info' => 'Invalid access key.']]
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CurrencyLayer returned an error: Invalid access key.');
        $service->getConversionDefinitions();
    }

    /**
     * Test getConversionDefinitions() throws with a generic message when the error has no info.
     */
    public function testGetErrorMessageWithoutInfo(): void
    {
        $service = self::createService(['success' => false, 'error' => ['code' => 101]]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CurrencyLayer returned an error: Unknown error');
        $service->getConversionDefinitions();
    }

    #endregion

    #region Method getRates() tests.

    /**
     * Test getConversionDefinitions() strips the source code from the quote keys, skipping the base.
     */
    public function testGetRatesStripsSourceFromQuoteKeys(): void
    {
        $service = self::createService(
            ['success' => true, 'source' => 'USD', 'quotes' => ['USDUSD' => 1, 'USDEUR' => 0.9, 'USDJPY' => 150]]
        );

        $this->assertSame([['USD', 'EUR', 0.9], ['USD', 'JPY', 150.0]], $service->getConversionDefinitions());
    }

    /**
     * Test getConversionDefinitions() uses USD when the response has no source.
     */
    public function testGetRatesDefaultsBaseToUsd(): void
    {
        $service = self::createService(['quotes' => ['USDEUR' => 0.9]]);

        $this->assertSame([['USD', 'EUR', 0.9]], $service->getConversionDefinitions());
    }

    /**
     * Test getConversionDefinitions() throws when the quotes are missing.
     */
    public function testGetRatesThrowsWhenQuotesMissing(): void
    {
        $service = self::createService(['success' => true]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing or invalid quotes in CurrencyLayer response.');
        $service->getConversionDefinitions();
    }

    #endregion
}
