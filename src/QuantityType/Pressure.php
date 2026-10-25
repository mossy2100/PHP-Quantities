<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\QuantityType;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Quantity;
use Mossy2100\Quantities\Services\PrefixService;
use Override;

/**
 * Represents pressure quantities.
 */
class Pressure extends Quantity
{
    #region Overridden methods

    /**
     * Unit definitions for pressure.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'pascal' => [
                'asciiSymbol' => 'Pa',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems' => [UnitSystem::Si],
            ],
            'atmosphere' => ['asciiSymbol' => 'atm', 'systems' => [UnitSystem::Scientific]],
            'mmHg' => ['asciiSymbol' => 'mmHg', 'systems' => [UnitSystem::Scientific]],
            'inHg' => ['asciiSymbol' => 'inHg', 'systems' => [UnitSystem::UsCustomary]],
        ];
    }

    /**
     * Conversion factors for pressure units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [['Pa', 'kg*m-1*s-2', 1], ['mmHg', 'Pa', 133.322387415], ['atm', 'Pa', 101325], ['inHg', 'mmHg', 25.4]];
    }

    #endregion
}
