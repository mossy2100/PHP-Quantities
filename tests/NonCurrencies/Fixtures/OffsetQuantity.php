<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Fixtures;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Quantity;
use Override;

/**
 * Test fixture: a quantity type with offset units.
 *
 * 'otu' is offset from its zero unit 'ztu' by 10, i.e. x otu is (x + 10) ztu, and 1 ztu is 2 m4. 'ftu' is offset from
 * 'ztu' by 0.25, which gives a non-integer combined offset, and 1 ttu is 3 ztu, which gives an inexact factor.
 */
class OffsetQuantity extends Quantity
{
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'offset test unit' => ['asciiSymbol' => 'otu', 'systems' => [UnitSystem::Custom]],
            'zero test unit' => ['asciiSymbol' => 'ztu', 'systems' => [UnitSystem::Custom]],
            'fractional offset test unit' => ['asciiSymbol' => 'ftu', 'systems' => [UnitSystem::Custom]],
            'triple test unit' => ['asciiSymbol' => 'ttu', 'systems' => [UnitSystem::Custom]],
        ];
    }

    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [['otu', 'ztu', 1.0], ['ztu', 'm4', 2.0], ['ftu', 'ztu', 1.0], ['ttu', 'ztu', 3.0]];
    }

    #[Override]
    public static function getOffsetDefinitions(): array
    {
        return [['otu', 'ztu', 10.0], ['ftu', 'ztu', 0.25]];
    }
}
