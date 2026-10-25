<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\CompoundUnit;

use Mossy2100\Quantities\Units\CompoundUnit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the binary arithmetic methods of CompoundUnit.
 */
#[CoversClass(CompoundUnit::class)]
class CompoundUnitBinaryArithmeticTest extends TestCase
{
    #region Method mul() tests.

    /**
     * Test mul() combines two simple units.
     */
    public function testMulCombinesSimpleUnits(): void
    {
        $a = CompoundUnit::parse('kg');
        $b = CompoundUnit::parse('m');
        $result = $a->mul($b);

        $this->assertSame('kg*m', $result->format(true));
        $this->assertSame('ML', $result->dimension);
    }

    /**
     * Test mul() combines exponents of the same unit.
     */
    public function testMulCombinesSameUnitExponents(): void
    {
        $a = CompoundUnit::parse('m');
        $b = CompoundUnit::parse('m2');
        $result = $a->mul($b);

        $this->assertSame('m3', $result->format(true));
        $this->assertSame('L3', $result->dimension);
    }

    /**
     * Test mul() cancels units with opposite exponents.
     */
    public function testMulCancelsOppositeExponents(): void
    {
        $a = CompoundUnit::parse('m');
        $b = CompoundUnit::parse('m-1');
        $result = $a->mul($b);

        $this->assertSame('', $result->format(true));
        $this->assertTrue($result->isDimensionless());
    }

    /**
     * Test mul() with compound units.
     */
    public function testMulCompoundUnits(): void
    {
        $a = CompoundUnit::parse('kg*m');
        $b = CompoundUnit::parse('s-2');
        $result = $a->mul($b);

        $this->assertSame('kg*m/s2', $result->format(true));
        $this->assertSame('MLT-2', $result->dimension);
    }

    /**
     * Test mul() with empty CompoundUnit is identity.
     */
    public function testMulWithEmptyIsIdentity(): void
    {
        $a = CompoundUnit::parse('m/s');
        $b = new CompoundUnit();
        $result = $a->mul($b);

        $this->assertSame('m/s', $result->format(true));
    }

    /**
     * Test mul() doesn't carry over the cached expansion of the original unit.
     */
    public function testMulDoesNotKeepCachedExpansion(): void
    {
        $newton = CompoundUnit::parse('N');
        $newton->tryExpand();

        $newtonMetre = $newton->mul(CompoundUnit::parse('m'));

        $this->assertSame('kg*m2/s2', $newtonMetre->tryExpand()?->compoundUnit->asciiSymbol);
    }

    /**
     * Test mul() does not modify the original.
     */
    public function testMulDoesNotModifyOriginal(): void
    {
        $a = CompoundUnit::parse('m');
        $b = CompoundUnit::parse('s');
        $result = $a->mul($b);

        $this->assertSame('m', $a->format(true));
        $this->assertSame('s', $b->format(true));
        $this->assertSame('m*s', $result->format(true));
    }

    #endregion
}
