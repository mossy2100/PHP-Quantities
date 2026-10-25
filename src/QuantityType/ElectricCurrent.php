<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\QuantityType;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Quantity;
use Mossy2100\Quantities\Services\PrefixService;
use Override;

/**
 * Represents electric current quantities.
 */
class ElectricCurrent extends Quantity
{
    #region Overridden methods

    /**
     * Unit definitions for electric current.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'ampere' => [
                'asciiSymbol' => 'A',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
        ];
    }

    #endregion
}
