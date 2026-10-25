<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Currencies\ExchangeRateServices;

use DomainException;
use Override;

/**
 * Exchange rate service using the Open Exchange Rates API.
 *
 * The free tier provides USD-based rates only.
 *
 * @see https://openexchangerates.org/
 */
class OpenExchangeRatesService extends AbstractExchangeRateService
{
    #region Private constants

    /**
     * The API endpoint for latest exchange rates.
     */
    private const string API_URL = 'https://openexchangerates.org/api/latest.json';

    #endregion

    #region Public properties

    /**
     * The App ID for Open Exchange Rates.
     */
    private(set) string $appId;

    #endregion

    #region Constructor

    /**
     * Create a new Open Exchange Rates service.
     *
     * After you create an account on Open Exchange Rates, you can find your App ID here:
     * https://openexchangerates.org/account/app-ids
     *
     * @param string $appId The App ID for Open Exchange Rates.
     * @throws DomainException If the App ID is empty.
     */
    public function __construct(string $appId)
    {
        if (empty($appId)) {
            throw new DomainException('App ID cannot be empty.');
        }

        $this->appId = $appId;
    }

    #endregion

    #region Overrides

    /**
     * Human-readable name of this exchange rate service.
     */
    #[Override]
    public function getName(): string
    {
        return 'Open Exchange Rates';
    }

    #[Override]
    protected function getUrl(): string
    {
        return self::API_URL . '?app_id=' . urlencode($this->appId);
    }

    #[Override]
    protected function getErrorMessage(array $data): ?string
    {
        if (!isset($data['error']) || $data['error'] !== true) {
            return null;
        }

        return isset($data['message']) && is_string($data['message']) ? $data['message'] : 'Unknown error';
    }

    #[Override]
    protected function getRates(array $data): array
    {
        return ['USD', $this->getArrayField($data, 'rates')];
    }

    #endregion
}
