<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Currencies\ExchangeRateServices;

use DomainException;
use Override;

/**
 * Exchange rate service using the Fixer.io API.
 *
 * The free tier provides EUR-based rates only, with 10,000 requests per month.
 *
 * @see https://fixer.io/
 */
class FixerService extends AbstractExchangeRateService
{
    #region Private constants

    /**
     * The API endpoint for latest exchange rates.
     */
    private const string API_URL = 'https://data.fixer.io/api/latest';

    #endregion

    #region Public properties

    /**
     * The access key for Fixer.io.
     */
    private(set) string $accessKey;

    #endregion

    #region Constructor

    /**
     * Create a new Fixer.io service.
     *
     * @param string $accessKey The access key for Fixer.io.
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
        return 'Fixer.io';
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

    #[Override]
    protected function getRates(array $data): array
    {
        return [$this->getStringField($data, 'base', 'EUR'), $this->getArrayField($data, 'rates')];
    }

    #endregion
}
