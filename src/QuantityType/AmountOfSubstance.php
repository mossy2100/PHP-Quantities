<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\QuantityType;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Quantity;
use Mossy2100\Quantities\Services\PrefixService;
use Override;

/**
 * Represents amount of substance quantities.
 */
class AmountOfSubstance extends Quantity
{
    #region Overridden methods

    /**
     * Unit definitions for amount of substance.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'mole' => [
                'asciiSymbol' => 'mol',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
        ];
    }

    #endregion
}
