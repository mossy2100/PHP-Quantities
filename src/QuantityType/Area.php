<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\QuantityType;

use Mossy2100\Quantities\Internal\UnitSystem;
use Mossy2100\Quantities\Quantity;
use Override;

/**
 * Represents area quantities.
 */
class Area extends Quantity
{
    #region Overridden methods

    /**
     * Unit definitions for area.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'hectare' => [
                'asciiSymbol' => 'ha',
                'systems'     => [UnitSystem::SiAccepted, UnitSystem::Metric],
            ],
            'acre'    => [
                'asciiSymbol' => 'ac',
                'systems'     => [UnitSystem::Imperial, UnitSystem::UsCustomary],
            ],
        ];
    }

    /**
     * Conversion factors for area units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            // Metric
            ['ha', 'm2', 10000],
            // US customary
            ['ac', 'yd2', 4840],
        ];
    }

    #endregion
}
