<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Currencies\ExchangeRateServices;

use DomainException;
use Override;
use RuntimeException;

/**
 * Exchange rate service using the CurrencyLayer API.
 *
 * The free tier provides USD-based rates only, with 1,000 requests per month.
 *
 * @see https://currencylayer.com/
 */
class CurrencyLayerService extends AbstractExchangeRateService
{
    #region Private constants

    /**
     * The API endpoint for latest exchange rates.
     */
    private const string API_URL = 'https://api.currencylayer.com/live';

    /**
     * The length of the source currency code prefix in quote keys.
     *
     * CurrencyLayer returns quote keys like "USDEUR", "USDGBP", etc.
     * The target currency code starts at this offset.
     */
    private const int SOURCE_CODE_LENGTH = 3;

    #endregion

    #region Public properties

    /**
     * The access key for CurrencyLayer.
     */
    private(set) string $accessKey;

    #endregion

    #region Constructor

    /**
     * Create a new CurrencyLayer service.
     *
     * @param string $accessKey The access key for CurrencyLayer.
     * @throws DomainException If the access key is empty.
     */
    public function __construct(string $accessKey)
    {
        if (empty($accessKey)) {
            throw new DomainException('Access key cannot be empty.');
        }

        $this->accessKey = $accessKey;
    }

    #endregion

    #region Overrides

    /**
     * Human-readable name of this exchange rate service.
     */
    #[Override]
    public function getName(): string
    {
        return 'CurrencyLayer';
    }

    #[Override]
    protected function getUrl(): string
    {
        return self::API_URL . '?access_key=' . urlencode($this->accessKey);
    }

    #[Override]
    protected function getErrorMessage(array $data): ?string
    {
        if (!isset($data['success']) || $data['success'] !== false) {
            return null;
        }

        $error = $data['error'] ?? null;
        $info = is_array($error) ? ($error['info'] ?? null) : null;
        return is_string($info) ? $info : 'Unknown error';
    }

    /**
     * Get the base currency code and the rates from an API response.
     *
     * Quote keys are concatenated currency codes (e.g. "USDEUR"), so the source code prefix is stripped to get the
     * target code.
     *
     * @param array<array-key, mixed> $data The decoded response.
     * @return array{string, array<array-key, mixed>} The base currency code, and the rates keyed by currency code.
     * @throws RuntimeException If the quotes are missing or invalid.
     */
    #[Override]
    protected function getRates(array $data): array
    {
        $rates = [];
        foreach ($this->getArrayField($data, 'quotes') as $quoteKey => $rate) {
            $rates[substr((string) $quoteKey, self::SOURCE_CODE_LENGTH)] = $rate;
        }

        return [$this->getStringField($data, 'source', 'USD'), $rates];
    }

    #endregion
}
