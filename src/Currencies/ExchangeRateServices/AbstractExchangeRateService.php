<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Currencies\ExchangeRateServices;

use Mossy2100\Core\Numbers;
use Override;
use RuntimeException;

/**
 * Base class for exchange rate services that fetch rates as JSON from a web API.
 *
 * Subclasses provide the URL, how to detect an error response, and where to find the rates in the response.
 *
 * @internal Shared implementation for the bundled services. Custom services should implement
 * ExchangeRateServiceInterface.
 */
abstract class AbstractExchangeRateService implements ExchangeRateServiceInterface
{
    #region Overrides

    /**
     * Fetch fresh exchange rates from the API and generate the conversion definitions.
     *
     * @return list<array{string, string, float}> Currency conversion definitions.
     * @throws RuntimeException If the API request fails, returns an error, or returns invalid data.
     */
    #[Override]
    public function getConversionDefinitions(): array
    {
        $data = $this->fetchJson($this->getUrl());

        // Check for an API error response.
        $message = $this->getErrorMessage($data);
        if ($message !== null) {
            throw new RuntimeException($this->getName() . " returned an error: $message");
        }

        [$base, $rates] = $this->getRates($data);
        return $this->buildDefinitions($base, $rates);
    }

    #endregion

    #region Hook methods

    /**
     * Get the URL to fetch the latest exchange rates from, including any key.
     *
     * @return string The URL.
     */
    abstract protected function getUrl(): string;

    /**
     * Get the error message from an API response, if it's an error response.
     *
     * The default returns null, for APIs without an error response format.
     *
     * @param array<array-key, mixed> $data The decoded response.
     * @return ?string The error message, or null if the response isn't an error.
     */
    protected function getErrorMessage(array $data): ?string
    {
        return null;
    }

    /**
     * Get the base currency code and the rates from an API response.
     *
     * @param array<array-key, mixed> $data The decoded response.
     * @return array{string, array<array-key, mixed>} The base currency code, and the rates keyed by currency code.
     * @throws RuntimeException If the rates are missing or invalid.
     */
    abstract protected function getRates(array $data): array;

    #endregion

    #region Helper methods

    /**
     * Fetch a URL and decode the JSON response.
     *
     * @param string $url The URL to fetch.
     * @return array<array-key, mixed> The decoded response.
     * @throws RuntimeException If the request fails or the response isn't a JSON object or array.
     */
    protected function fetchJson(string $url): array
    {
        $response = @file_get_contents($url);
        if ($response === false) {
            throw new RuntimeException('Failed to fetch exchange rates from ' . $this->getName() . '.');
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            throw new RuntimeException('Invalid JSON response from ' . $this->getName() . '.');
        }

        return $data;
    }

    /**
     * Get an array field from an API response.
     *
     * @param array<array-key, mixed> $data The decoded response.
     * @param string $key The field name.
     * @return array<array-key, mixed> The field value.
     * @throws RuntimeException If the field is missing or isn't an array.
     */
    protected function getArrayField(array $data, string $key): array
    {
        if (!isset($data[$key]) || !is_array($data[$key])) {
            throw new RuntimeException("Missing or invalid $key in " . $this->getName() . ' response.');
        }

        return $data[$key];
    }

    /**
     * Get a string field from an API response, or a default if it's missing or isn't a string.
     *
     * @param array<array-key, mixed> $data The decoded response.
     * @param string $key The field name.
     * @param string $default The value to return if the field is missing or isn't a string.
     * @return string The field value, or the default.
     */
    protected function getStringField(array $data, string $key, string $default): string
    {
        return isset($data[$key]) && is_string($data[$key]) ? $data[$key] : $default;
    }

    /**
     * Generate conversion definitions from a base currency and its rates.
     *
     * Skips the base currency, and any rate that isn't a positive number. JSON decodes a whole-number rate as an int,
     * so it's cast to float.
     *
     * @param string $base The base currency code.
     * @param array<array-key, mixed> $rates The rates keyed by currency code.
     * @return list<array{string, string, float}> Currency conversion definitions.
     */
    protected function buildDefinitions(string $base, array $rates): array
    {
        $conversionDefinitions = [];
        foreach ($rates as $currencyCode => $rate) {
            $currencyCode = (string) $currencyCode;
            if ($currencyCode === $base || !Numbers::isNumber($rate) || $rate <= 0) {
                continue;
            }
            $conversionDefinitions[] = [$base, $currencyCode, (float) $rate];
        }
        return $conversionDefinitions;
    }

    #endregion
}
