<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Currencies\ExchangeRateServices;

use Mossy2100\Quantities\Currencies\ExchangeRateServices\FrankfurterService;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;

/**
 * Offline tests for FrankfurterService, using canned API responses.
 *
 * The live API is tested in tests/Currencies/ExchangeRateServices/FrankfurterServiceTest.php.
 */
#[CoversClass(FrankfurterService::class)]
class FrankfurterServiceTest extends TestCase
{
    #region Helper methods

    /**
     * Create a service that returns the given response instead of calling the API.
     *
     * @param array<array-key, mixed> $response The decoded API response.
     * @return FrankfurterService The service.
     */
    private static function createService(array $response): FrankfurterService
    {
        return new class ($response) extends FrankfurterService {
            /**
             * @param array<array-key, mixed> $response The decoded API response.
             */
            public function __construct(private readonly array $response)
            {
            }

            #[Override]
            protected function fetchJson(string $url): array
            {
                return $this->response;
            }
        };
    }

    #endregion

    #region Method getName() tests.

    /**
     * Test getName() returns the service name.
     */
    public function testGetName(): void
    {
        $service = new FrankfurterService();
        $this->assertSame('Frankfurter (ECB)', $service->getName());
    }

    #endregion

    #region Method getUrl() tests.

    /**
     * Test getUrl() returns the endpoint, which needs no key.
     */
    public function testGetUrl(): void
    {
        $service = new FrankfurterService();

        $this->assertSame(
            'https://api.frankfurter.dev/v1/latest',
            new ReflectionMethod($service, 'getUrl')->invoke($service)
        );
    }

    #endregion

    #region Method getRates() tests.

    /**
     * Test getConversionDefinitions() maps the rates from the base currency, skipping the base.
     */
    public function testGetRatesMapsRates(): void
    {
        $service = self::createService(['base' => 'EUR', 'rates' => ['EUR' => 1, 'USD' => 1.1]]);

        $this->assertSame([['EUR', 'USD', 1.1]], $service->getConversionDefinitions());
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
        $service = self::createService(['message' => 'not found']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing or invalid rates in Frankfurter (ECB) response.');
        $service->getConversionDefinitions();
    }

    #endregion
}
