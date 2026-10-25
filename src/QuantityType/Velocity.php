<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\QuantityType;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Quantity;
use Override;

/**
 * Represents velocity quantities.
 */
class Velocity extends Quantity
{
    #region Overridden methods

    /**
     * Unit definitions for velocity.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'knot' => [
                'asciiSymbol' => 'kn',
                'systems'     => [UnitSystem::Nautical],
            ],
        ];
    }

    /**
     * Conversion factors for velocity units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['kn', 'nmi*h-1', 1],
        ];
    }

    #endregion
}
