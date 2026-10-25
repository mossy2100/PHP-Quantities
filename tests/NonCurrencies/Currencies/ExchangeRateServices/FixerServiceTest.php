<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Currencies\ExchangeRateServices;

use DomainException;
use Mossy2100\Quantities\Currencies\ExchangeRateServices\FixerService;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;

/**
 * Offline tests for FixerService, using canned API responses.
 *
 * The live API is tested in tests/Currencies/ExchangeRateServices/FixerServiceTest.php.
 */
#[CoversClass(FixerService::class)]
class FixerServiceTest extends TestCase
{
    #region Helper methods

    /**
     * Create a service that returns the given response instead of calling the API.
     *
     * @param array<array-key, mixed> $response The decoded API response.
     * @return FixerService The service.
     */
    private static function createService(array $response): FixerService
    {
        return new class ($response) extends FixerService {
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
        new FixerService('');
    }

    /**
     * Test the constructor stores the access key.
     */
    public function testConstructorStoresAccessKey(): void
    {
        $service = new FixerService('test-key');
        $this->assertSame('test-key', $service->accessKey);
    }

    #endregion

    #region Method getName() tests.

    /**
     * Test getName() returns the service name.
     */
    public function testGetName(): void
    {
        $service = new FixerService('test-key');
        $this->assertSame('Fixer.io', $service->getName());
    }

    #endregion

    #region Method getUrl() tests.

    /**
     * Test getUrl() includes the URL-encoded access key.
     */
    public function testGetUrl(): void
    {
        $service = new FixerService('key&1');

        $this->assertSame(
            'https://data.fixer.io/api/latest?access_key=key%261',
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
        $this->expectExceptionMessage('Fixer.io returned an error: Invalid access key.');
        $service->getConversionDefinitions();
    }

    /**
     * Test getConversionDefinitions() throws with a generic message when the error has no info.
     */
    public function testGetErrorMessageWithoutInfo(): void
    {
        $service = self::createService(['success' => false, 'error' => ['code' => 101]]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Fixer.io returned an error: Unknown error');
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
            ['success' => true, 'base' => 'EUR', 'rates' => ['EUR' => 1, 'USD' => 1.1, 'JPY' => 160]]
        );

        $this->assertSame([['EUR', 'USD', 1.1], ['EUR', 'JPY', 160.0]], $service->getConversionDefinitions());
    }

    /**
     * Test getConversionDefinitions() uses EUR when the response has no base.
     */
    public function testGetRatesDefaultsBaseToEur(): void
    {
        $service = self::createService(['rates' => ['USD' => 1.1]]);

        $this->assertSame([['EUR', 'USD', 1.1]], $service->getConversionDefinitions());
    }

    /**
     * Test getConversionDefinitions() throws when the rates are missing.
     */
    public function testGetRatesThrowsWhenRatesMissing(): void
    {
        $service = self::createService(['success' => true]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing or invalid rates in Fixer.io response.');
        $service->getConversionDefinitions();
    }

    #endregion
}
