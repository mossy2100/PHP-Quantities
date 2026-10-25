<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\QuantityType;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Quantity;
use Mossy2100\Quantities\Services\PrefixService;
use Override;

/**
 * Represents capacitance quantities.
 */
class Capacitance extends Quantity
{
    #region Overridden methods

    /**
     * Unit definitions for capacitance.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'farad' => [
                'asciiSymbol' => 'F',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems' => [UnitSystem::Si],
            ],
        ];
    }

    /**
     * Conversion factors for capacitance units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [['F', 'kg-1*m-2*s4*A2', 1]];
    }

    #endregion
}
