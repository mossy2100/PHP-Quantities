<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\QuantityType;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Quantity;
use Override;

/**
 * Represents dimensionless quantities.
 */
class Dimensionless extends Quantity
{
    #region Overridden methods

    /**
     * Unit definitions for dimensionless quantities.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'scalar' => ['asciiSymbol' => '', 'systems' => [UnitSystem::Common]],
            'percentage' => ['asciiSymbol' => '%', 'systems' => [UnitSystem::Common]],
            'parts per thousand' => [
                'asciiSymbol' => 'permil',
                'unicodeSymbol' => '‰',
                'systems' => [UnitSystem::Common],
            ],
            'parts per million' => ['asciiSymbol' => 'ppm', 'systems' => [UnitSystem::Common]],
            'parts per billion' => ['asciiSymbol' => 'ppb', 'systems' => [UnitSystem::Common]],
            'parts per trillion' => ['asciiSymbol' => 'ppt', 'systems' => [UnitSystem::Common]],
        ];
    }

    /**
     * Conversion factors for dimensionless quantities.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['', '%', 100],
            ['%', 'permil', 10],
            ['permil', 'ppm', 1000],
            ['ppm', 'ppb', 1000],
            ['ppb', 'ppt', 1000],
        ];
    }

    #endregion
}
