<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Fixtures;

use Mossy2100\Quantities\Quantity;
use Override;

/**
 * Fixture with a conversion definition referencing an unknown destination unit.
 */
class UnknownDestQuantity extends Quantity
{
    /**
     * Returns a conversion where the destination unit symbol is unregistered.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [['m', 'zzzdest', 1.0]];
    }
}
