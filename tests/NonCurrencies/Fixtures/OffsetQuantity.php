<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Fixtures;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Quantity;
use Override;

/**
 * Test fixture: a quantity type with an offset unit.
 *
 * 'otu' is offset from its zero unit 'ztu' by 10, i.e. x otu is (x + 10) ztu, and 1 ztu is 2 m4.
 */
class OffsetQuantity extends Quantity
{
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'offset test unit' => [
                'asciiSymbol' => 'otu',
                'systems'     => [
                    UnitSystem::Custom,
                ],
            ],
            'zero test unit'   => [
                'asciiSymbol' => 'ztu',
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
            ['otu', 'ztu', 1.0],
            ['ztu', 'm4', 2.0],
        ];
    }

    #[Override]
    public static function getOffsetDefinitions(): array
    {
        return [
            ['otu', 'ztu', 10.0],
        ];
    }
}
