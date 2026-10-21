<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\QuantityType;

use Mossy2100\Quantities\Internal\UnitSystem;
use Mossy2100\Quantities\Quantity;
use Mossy2100\Quantities\Services\PrefixService;
use Override;

/**
 * Represents inductance quantities.
 */
class Inductance extends Quantity
{
    #region Overridden methods

    /**
     * Unit definitions for inductance.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'henry' => [
                'asciiSymbol' => 'H',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
        ];
    }

    /**
     * Conversion factors for inductance units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['H', 'kg*m2*s-2*A-2', 1],
        ];
    }

    #endregion
}
