<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\QuantityType;

use Mossy2100\Quantities\Internal\UnitSystem;
use Mossy2100\Quantities\Quantity;
use Mossy2100\Quantities\Services\PrefixService;
use Override;

/**
 * Represents frequency quantities.
 */
class Frequency extends Quantity
{
    #region Overridden methods

    /**
     * Unit definitions for frequency.
     *
     * Note: Becquerel has the same dimension (T-1) but measures radioactivity,
     * not frequency. It's included here as they share the same dimension code.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'hertz'     => [
                'asciiSymbol' => 'Hz',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
            'becquerel' => [
                'asciiSymbol' => 'Bq',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
        ];
    }

    /**
     * Conversion factors for frequency units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['Hz', 's-1', 1],
            ['Bq', 's-1', 1],
        ];
    }

    #endregion
}
