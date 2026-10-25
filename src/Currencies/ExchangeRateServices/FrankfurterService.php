<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Currencies\ExchangeRateServices;

use Mossy2100\Core\Numbers;
use Override;
use RuntimeException;

/**
 * Exchange rate service using the Frankfurter API, backed by European Central Bank data.
 *
 * Completely free with no API key required. Rates are updated daily around 16:00 CET.
 *
 * @see https://frankfurter.dev/
 */
class FrankfurterService implements ExchangeRateServiceInterface
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

    /**
     * Fetch fresh exchange rates from the Frankfurter API and generate the conversion definitions.
     *
     * Rates are relative to EUR (the ECB's base currency).
     *
     * @return list<array{string, string, float}> Currency conversion definitions.
     * @throws RuntimeException If the API request fails or returns invalid data.
     */
    #[Override]
    public function getConversionDefinitions(): array
    {
        $response = @file_get_contents(self::API_URL);
        if ($response === false) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Failed to fetch exchange rates from Frankfurter API.');
            // @codeCoverageIgnoreEnd
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Invalid JSON response from Frankfurter API.');
            // @codeCoverageIgnoreEnd
        }

        if (!isset($data['rates']) || !is_array($data['rates'])) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Missing or invalid rates in Frankfurter API response.');
            // @codeCoverageIgnoreEnd
        }

        $base = isset($data['base']) && is_string($data['base']) ? $data['base'] : 'EUR';

        /** @var array<array-key, mixed> $rates */
        $rates = $data['rates'];

        // Convert to conversion definitions.
        $conversionDefinitions = [];
        foreach ($rates as $currencyCode => $rate) {
            // Skip the base currency, and any rate that isn't a positive number. JSON decodes a whole-number rate as
            // an int, so cast it to float.
            if ($currencyCode === $base || !Numbers::isNumber($rate) || $rate <= 0) {
                continue;
            }
            $conversionDefinitions[] = [$base, (string) $currencyCode, (float) $rate];
        }
        return $conversionDefinitions;
    }

    #endregion
}
