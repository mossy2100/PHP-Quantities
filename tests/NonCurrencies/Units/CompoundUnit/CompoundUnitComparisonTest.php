<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\CompoundUnit;

use Mossy2100\Quantities\Units\CompoundUnit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the comparison methods of CompoundUnit.
 */
#[CoversClass(CompoundUnit::class)]
class CompoundUnitComparisonTest extends TestCase
{
    #region Method equal() tests.

    public function testEqualSameUnits(): void
    {
        $du1 = CompoundUnit::parse('m/s');
        $du2 = CompoundUnit::parse('m/s');

        $this->assertTrue($du1->equal($du2));
    }

    public function testEqualCompoundUnits(): void
    {
        $du1 = CompoundUnit::parse('kg*m/s2');
        $du2 = CompoundUnit::parse('kg*m*s-2');

        $this->assertTrue($du1->equal($du2));
    }

    public function testEqualDifferentUnits(): void
    {
        $du1 = CompoundUnit::parse('m');
        $du2 = CompoundUnit::parse('km');

        $this->assertFalse($du1->equal($du2));
    }

    public function testEqualDifferentExponents(): void
    {
        $du1 = CompoundUnit::parse('m2');
        $du2 = CompoundUnit::parse('m3');

        $this->assertFalse($du1->equal($du2));
    }

    public function testEqualDifferentNumberOfTerms(): void
    {
        $du1 = CompoundUnit::parse('m');
        $du2 = CompoundUnit::parse('m*s');

        $this->assertFalse($du1->equal($du2));
    }

    public function testEqualEmptyUnits(): void
    {
        $du1 = new CompoundUnit();
        $du2 = new CompoundUnit();

        $this->assertTrue($du1->equal($du2));
    }

    public function testEqualWithNonCompoundUnit(): void
    {
        $du = CompoundUnit::parse('m');

        $this->assertFalse($du->equal('m'));
        $this->assertFalse($du->equal(null));
        $this->assertFalse($du->equal(123));
    }

    public function testEqualDifferentPrefixes(): void
    {
        $du1 = CompoundUnit::parse('km');
        $du2 = CompoundUnit::parse('mm');

        $this->assertFalse($du1->equal($du2));
    }

    #endregion
}
