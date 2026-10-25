<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\CompoundUnit;

use Mossy2100\Quantities\Units\CompoundUnit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the power methods of CompoundUnit.
 */
#[CoversClass(CompoundUnit::class)]
class CompoundUnitPowerTest extends TestCase
{
    #region Method pow() tests.

    /**
     * Test pow squares a simple unit.
     */
    public function testPowSquaresSimpleUnit(): void
    {
        $du = CompoundUnit::parse('m');
        $result = $du->pow(2);

        $this->assertSame('m2', $result->format(true));
        $this->assertSame('L2', $result->dimension);
    }

    /**
     * Test pow cubes a simple unit.
     */
    public function testPowCubesSimpleUnit(): void
    {
        $du = CompoundUnit::parse('m');
        $result = $du->pow(3);

        $this->assertSame('m3', $result->format(true));
        $this->assertSame('L3', $result->dimension);
    }

    /**
     * Test pow applies to all terms in a compound unit.
     */
    public function testPowAppliesToAllTermsInCompoundUnit(): void
    {
        // (m/s)^2 = m2/s2
        $du = CompoundUnit::parse('m/s');
        $result = $du->pow(2);

        $this->assertSame('m2/s2', $result->format(true));
        $this->assertSame('L2T-2', $result->dimension);
    }

    /**
     * Test pow with negative exponent inverts the unit.
     */
    public function testPowWithNegativeExponentInvertsUnit(): void
    {
        // m^-1 = m-1
        $du = CompoundUnit::parse('m');
        $result = $du->pow(-1);

        $this->assertSame('m-1', $result->format(true));
        $this->assertSame('L-1', $result->dimension);
    }

    /**
     * Test pow does not modify the original.
     */
    public function testPowDoesNotModifyOriginal(): void
    {
        $du = CompoundUnit::parse('m');
        $result = $du->pow(2);

        $this->assertSame('m', $du->format(true));
        $this->assertSame('m2', $result->format(true));
    }

    /**
     * Test pow preserves prefixes.
     */
    public function testPowPreservesPrefixes(): void
    {
        $du = CompoundUnit::parse('km');
        $result = $du->pow(2);

        $this->assertSame('km2', $result->format(true));
    }

    /**
     * Test pow with exponent 1 returns equivalent unit.
     */
    public function testPowWithExponentOneReturnsEquivalentUnit(): void
    {
        $du = CompoundUnit::parse('m/s');
        $result = $du->pow(1);

        $this->assertTrue($du->equal($result));
    }

    /**
     * Test pow on unit that already has exponents.
     */
    public function testPowOnUnitWithExistingExponents(): void
    {
        // (m2/s)^2 = m4/s2
        $du = CompoundUnit::parse('m2/s');
        $result = $du->pow(2);

        $this->assertSame('m4/s2', $result->format(true));
    }

    #endregion
}
