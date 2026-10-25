<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Fixtures;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Quantity;
use Override;

/**
 * Test fixture: a quantity type with an invalid offset definition, whose unit is a different size to its zero unit.
 */
class InvalidOffsetQuantity extends Quantity
{
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'bad offset unit' => [
                'asciiSymbol' => 'bou',
                'systems'     => [
                    UnitSystem::Custom,
                ],
            ],
            'bad zero unit'   => [
                'asciiSymbol' => 'bzu',
                'systems'     => [
                    UnitSystem::Custom,
                ],
            ],
        ];
    }

    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['bou', 'bzu', 2.0],
        ];
    }

    #[Override]
    public static function getOffsetDefinitions(): array
    {
        return [
            ['bou', 'bzu', 10.0],
        ];
    }
}
