<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\QuantityType;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Quantity;
use Mossy2100\Quantities\Services\PrefixService;
use Override;

/**
 * Represents temperature quantities.
 */
class Temperature extends Quantity
{
    #region Public constants

    /**
     * Offset to convert Celsius to Kelvin.
     */
    public const float CELSIUS_OFFSET = 273.15;

    /**
     * Offset to convert Fahrenheit to Rankine.
     */
    public const float FAHRENHEIT_OFFSET = 459.67;

    /**
     * Factor to convert Kelvin to Rankine.
     */
    public const float RANKINE_PER_KELVIN = 1.8;

    #endregion

    #region Overridden methods

    /**
     * Unit definitions for temperature.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'kelvin'     => [
                'asciiSymbol' => 'K',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [
                    UnitSystem::Si,
                ],
            ],
            'celsius'    => [
                'asciiSymbol'     => 'degC',
                'unicodeSymbol'   => '°C',
                'alternateSymbol' => '℃',
                'systems'         => [
                    UnitSystem::Si,
                ],
            ],
            'fahrenheit' => [
                'asciiSymbol'     => 'degF',
                'unicodeSymbol'   => '°F',
                'alternateSymbol' => '℉',
                'systems'         => [
                    UnitSystem::Imperial,
                    UnitSystem::UsCustomary,
                ],
            ],
            'rankine'    => [
                'asciiSymbol'   => 'degR',
                'unicodeSymbol' => '°R',
                'systems'       => [
                    UnitSystem::Imperial,
                    UnitSystem::UsCustomary,
                ],
            ],
        ];
    }

    /**
     * Conversion factors for temperature units.
     *
     * These are the sizes of the units. The offsets for Celsius and Fahrenheit are in getOffsetDefinitions().
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['degC', 'K', 1],
            ['degF', 'degR', 1],
            [
                'K',
                'degR',
                self::RANKINE_PER_KELVIN,
            ],
        ];
    }

    /**
     * Offset definitions for temperature units.
     *
     * Celsius and Fahrenheit are measured from a point other than absolute zero, so converting a temperature in them
     * needs an offset as well as a factor. For example, x °C is (x + 273.15) K.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getOffsetDefinitions(): array
    {
        return [
            [
                'degC',
                'K',
                self::CELSIUS_OFFSET,
            ],
            [
                'degF',
                'degR',
                self::FAHRENHEIT_OFFSET,
            ],
        ];
    }

    #endregion

    #region Transformation methods

    /**
     * Convert the temperature to its corresponding absolute scale unit.
     *
     * Celsius converts to Kelvin; Fahrenheit converts to Rankine. Kelvin and Rankine are cloned unchanged.
     * Always returns a new object. Useful when a value measured from absolute zero is needed, e.g. for ratio
     * calculations.
     *
     * @return static The equivalent temperature in K or °R.
     */
    public function toAbsoluteScale(): static
    {
        return match ($this->compoundUnit->asciiSymbol) {
            'degC' => $this->to('K'),
            'degF' => $this->to('degR'),
            default => clone $this,
        };
    }

    #endregion
}
