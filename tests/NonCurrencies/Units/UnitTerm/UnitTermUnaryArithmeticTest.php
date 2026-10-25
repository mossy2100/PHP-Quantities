<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\UnitTerm;

use Mossy2100\Quantities\Units\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the unary arithmetic methods of UnitTerm.
 */
#[CoversClass(UnitTerm::class)]
final class UnitTermUnaryArithmeticTest extends TestCase
{
    #region Method recip() tests.

    /**
     * Test recip() negates exponent.
     */
    public function testRecipNegatesExponent(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $inverted = $term->recip();

        $this->assertSame(-2, $inverted->exponent);
        $this->assertSame('k', $inverted->prefix?->asciiSymbol);
        $this->assertSame($term->unit, $inverted->unit);
    }

    /**
     * Test recip() returns new instance.
     */
    public function testRecipReturnsNewInstance(): void
    {
        $term = new UnitTerm('m', null, 2);

        $inverted = $term->recip();

        $this->assertNotSame($term, $inverted);
    }

    #endregion
}
