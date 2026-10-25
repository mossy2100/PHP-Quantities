<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Currencies\ExchangeRateServices;

use DomainException;
use Override;

/**
 * Exchange rate service using the ExchangeRate-API.
 *
 * The free tier supports any base currency with 1,500 requests per month. This service fetches rates relative to USD.
 *
 * @see https://www.exchangerate-api.com/
 */
class ExchangeRateApiService extends AbstractExchangeRateService
{
    #region Private constants

    /**
     * The API endpoint for latest exchange rates.
     *
     * The API key and base currency are appended as path segments.
     */
    private const string API_URL = 'https://v6.exchangerate-api.com/v6';

    #endregion

    #region Public properties

    /**
     * The API key for ExchangeRate-API.
     */
    private(set) string $apiKey;

    #endregion

    #region Constructor

    /**
     * Create a new ExchangeRate-API service.
     *
     * After you create an account on ExchangeRate-API, you can find your API key in your dashboard.
     *
     * @param string $apiKey The API key for ExchangeRate-API.
     * @throws DomainException If the API key is empty.
     */
    public function __construct(string $apiKey)
    {
        if (empty($apiKey)) {
            throw new DomainException('API key cannot be empty.');
        }

        $this->apiKey = $apiKey;
    }

    #endregion

    #region Overrides

    /**
     * Human-readable name of this exchange rate service.
     */
    #[Override]
    public function getName(): string
    {
        return 'ExchangeRate-API';
    }

    #[Override]
    protected function getUrl(): string
    {
        return self::API_URL . '/' . urlencode($this->apiKey) . '/latest/USD';
    }

    #[Override]
    protected function getErrorMessage(array $data): ?string
    {
        if (!isset($data['result']) || $data['result'] !== 'error') {
            return null;
        }

        return isset($data['error-type']) && is_string($data['error-type']) ? $data['error-type'] : 'Unknown error';
    }

    #[Override]
    protected function getRates(array $data): array
    {
        return [$this->getStringField($data, 'base_code', 'USD'), $this->getArrayField($data, 'conversion_rates')];
    }

    #endregion
}
