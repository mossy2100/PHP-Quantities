<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\QuantityType;

use Mossy2100\Quantities\Currencies\CurrencyService;
use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Quantity;
use NumberFormatter;
use Override;

/**
 * Represents money quantities.
 */
class Money extends Quantity
{
    #region Overridden methods

    /**
     * Unit definitions for currencies.
     *
     * These come from the local currency data (see CurrencyService::getUnits()). Nothing is fetched.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        // The currencies are keyed by code, but unit definitions are keyed by name.
        $definitions = [];
        foreach (CurrencyService::getUnits()['currencies'] as $code => $name) {
            $definitions[$name] = ['asciiSymbol' => $code, 'systems' => [UnitSystem::Financial]];
        }
        return $definitions;
    }

    /**
     * Conversion factors for currency units.
     *
     * These come from the local exchange rates (see CurrencyService::getConversions()), or there are none if the
     * rates haven't been fetched yet. Nothing is fetched.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return CurrencyService::getConversions()['definitions'] ?? [];
    }

    /**
     * Convert the currency value to a string.
     *
     * For additional formatting options,
     * @see Quantity::format()
     *
     * @return string
     */
    #[Override]
    public function __toString(): string
    {
        // By default, use the locale-specific currency formatter.
        $locale = CurrencyService::getLocale();
        if ($locale !== null) {
            $fmt = new NumberFormatter($locale, NumberFormatter::CURRENCY);
            $result = $fmt->formatCurrency($this->value, (string) $this->compoundUnit);
            if ($result !== false) {
                return $result;
            }
        }

        // Fall back to format().
        return $this->format(); // @codeCoverageIgnore
    }

    #endregion
}
