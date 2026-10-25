<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Currencies\ExchangeRateServices;

use Override;

/**
 * Exchange rate service using the Frankfurter API, backed by European Central Bank data.
 *
 * Completely free with no API key required. Rates are relative to EUR, and updated daily around 16:00 CET.
 *
 * @see https://frankfurter.dev/
 */
class FrankfurterService extends AbstractExchangeRateService
{
    #region Private constants

    /**
     * The API endpoint for latest exchange rates.
     */
    private const string API_URL = 'https://api.frankfurter.dev/v1/latest';

    #endregion

    #region Overrides

    /**
     * Human-readable name of this exchange rate service.
     */
    #[Override]
    public function getName(): string
    {
        return 'Frankfurter (ECB)';
    }

    #[Override]
    protected function getUrl(): string
    {
        return self::API_URL;
    }

    #[Override]
    protected function getRates(array $data): array
    {
        return [$this->getStringField($data, 'base', 'EUR'), $this->getArrayField($data, 'rates')];
    }

    #endregion
}
