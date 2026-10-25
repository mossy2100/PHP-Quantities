<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Fixtures;

use Mossy2100\Quantities\Quantity;
use Override;

/**
 * Fixture with a conversion definition referencing an unknown source unit.
 */
class UnknownSrcQuantity extends Quantity
{
    /**
     * Returns a conversion where the source unit symbol is unregistered.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [['zzzsrc', 'm', 1.0]];
    }
}
