<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Fixtures;

use Mossy2100\Quantities\Currencies\ExchangeRateServices\ExchangeRateServiceInterface;
use Override;
use RuntimeException;

/**
 * Test fixture: an offline exchange rate service with fixed rates.
 *
 * Counts how many times rates are fetched, and can be made to fail, so tests can check when CurrencyService fetches
 * without calling a live API.
 */
class FakeExchangeRateService implements ExchangeRateServiceInterface
{
    /**
     * The number of times getConversionDefinitions() has been called.
     */
    public int $fetchCount = 0;

    /**
     * Constructor.
     *
     * @param string $name The service name.
     * @param bool $fail If true, getConversionDefinitions() throws instead of returning rates.
     * @param list<array{string, string, float}> $definitions The conversion definitions to return.
     */
    public function __construct(
        private readonly string $name = 'Fake',
        private readonly bool $fail = false,
        private readonly array $definitions = [['USD', 'EUR', 0.9], ['USD', 'GBP', 0.8]]
    ) {
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Return the fixed conversion definitions, or throw if the service is set to fail.
     *
     * @return list<array{string, string, float}>
     * @throws RuntimeException If the service is set to fail.
     */
    #[Override]
    public function getConversionDefinitions(): array
    {
        $this->fetchCount++;

        if ($this->fail) {
            throw new RuntimeException('Fake exchange rate service failure.');
        }

        return $this->definitions;
    }
}
