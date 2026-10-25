<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\QuantityType;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Quantity;
use Mossy2100\Quantities\Services\PrefixService;
use Override;

/**
 * Represents magnetic flux density quantities.
 */
class MagneticFluxDensity extends Quantity
{
    #region Overridden methods

    /**
     * Unit definitions for magnetic flux density.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'tesla' => [
                'asciiSymbol' => 'T',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
        ];
    }

    /**
     * Conversion factors for magnetic flux density units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['T', 'kg*s-2*A-1', 1],
        ];
    }

    #endregion
}
