<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\UnitTerm;

use Mossy2100\Quantities\Units\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the power methods of UnitTerm.
 */
#[CoversClass(UnitTerm::class)]
final class UnitTermPowerTest extends TestCase
{
    #region Method pow() tests.

    /**
     * Test pow() multiplies exponents.
     */
    public function testPowMultipliesExponents(): void
    {
        $term = new UnitTerm('m', null, 2);

        $newTerm = $term->pow(3);

        $this->assertSame(6, $newTerm->exponent);
    }

    #endregion
}
