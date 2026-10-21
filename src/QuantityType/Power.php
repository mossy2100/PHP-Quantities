<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\QuantityType;

use Mossy2100\Quantities\Internal\UnitSystem;
use Mossy2100\Quantities\Quantity;
use Mossy2100\Quantities\Services\PrefixService;
use Override;

/**
 * Represents power quantities.
 */
class Power extends Quantity
{
    #region Overridden methods

    /**
     * Unit definitions for power.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'watt' => [
                'asciiSymbol' => 'W',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
        ];
    }

    /**
     * Conversion factors for power units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['W', 'kg*m2*s-3', 1],
        ];
    }

    #endregion
}
