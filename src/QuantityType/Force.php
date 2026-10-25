<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\QuantityType;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\PhysicalConstant;
use Mossy2100\Quantities\Quantity;
use Mossy2100\Quantities\Services\PrefixService;
use Override;

/**
 * Represents force quantities.
 */
class Force extends Quantity
{
    #region Overridden methods

    /**
     * Unit definitions for force.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'newton'      => [
                'asciiSymbol' => 'N',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
            'pound force' => [
                'asciiSymbol' => 'lbf',
                'systems'     => [UnitSystem::Imperial, UnitSystem::UsCustomary],
            ],
        ];
    }

    /**
     * Conversion factors for force units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['N', 'kg*m/s2', 1],
            // g₀ (standard gravity) = (9.80665 m/s²) / (0.3048 m/ft).
            ['lbf', 'lb*ft/s2', PhysicalConstant::EARTH_GRAVITY / 0.3048],
        ];
    }

    #endregion
}
