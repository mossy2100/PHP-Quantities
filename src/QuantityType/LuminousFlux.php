<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\QuantityType;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Quantity;
use Mossy2100\Quantities\Services\PrefixService;
use Override;

/**
 * Represents luminous flux quantities.
 */
class LuminousFlux extends Quantity
{
    #region Overridden methods

    /**
     * Unit definitions for luminous flux.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'lumen' => [
                'asciiSymbol' => 'lm',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems' => [UnitSystem::Si],
            ],
        ];
    }

    /**
     * Conversion factors for luminous flux units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [['lm', 'cd*rad2', 1]];
    }

    #endregion
}
