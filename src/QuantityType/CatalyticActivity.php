<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\QuantityType;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Quantity;
use Mossy2100\Quantities\Services\PrefixService;
use Override;

/**
 * Represents catalytic activity quantities.
 */
class CatalyticActivity extends Quantity
{
    #region Overridden methods

    /**
     * Unit definitions for catalytic activity.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'katal' => [
                'asciiSymbol' => 'kat',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [
                    UnitSystem::Si,
                ],
            ],
        ];
    }

    /**
     * Conversion factors for catalytic activity units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['kat', 'mol*s-1', 1],
        ];
    }

    #endregion
}
