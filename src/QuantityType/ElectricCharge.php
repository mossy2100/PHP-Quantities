<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\QuantityType;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Quantity;
use Mossy2100\Quantities\Services\PrefixService;
use Override;

/**
 * Represents electric charge quantities.
 */
class ElectricCharge extends Quantity
{
    #region Overridden methods

    /**
     * Unit definitions for electric charge.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'coulomb' => [
                'asciiSymbol' => 'C',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [
                    UnitSystem::Si,
                ],
            ],
        ];
    }

    /**
     * Conversion factors for electric charge units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['C', 's*A', 1],
        ];
    }

    #endregion
}
