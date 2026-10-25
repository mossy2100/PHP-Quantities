<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\CompoundUnit;

use Mossy2100\Quantities\Services\UnitService;
use Mossy2100\Quantities\Units\CompoundUnit;
use Mossy2100\Quantities\Units\Unit;
use Mossy2100\Quantities\Units\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the inspection methods of CompoundUnit.
 */
#[CoversClass(CompoundUnit::class)]
class CompoundUnitInspectionTest extends TestCase
{
    #region Method isSi() tests.

    /**
     * Test isSi returns true for simple SI unit.
     */
    public function testIsSiReturnsTrueForSimpleSiUnit(): void
    {
        $du = CompoundUnit::parse('m');

        $this->assertTrue($du->isSi());
    }

    /**
     * Test isSi returns true for compound SI unit.
     */
    public function testIsSiReturnsTrueForCompoundSiUnit(): void
    {
        $du = CompoundUnit::parse('kg*m/s2');

        $this->assertTrue($du->isSi());
    }

    /**
     * Test isSi returns true for prefixed SI unit.
     */
    public function testIsSiReturnsTrueForPrefixedSiUnit(): void
    {
        $du = CompoundUnit::parse('km/s');

        $this->assertTrue($du->isSi());
    }

    /**
     * Test isSi returns false for Imperial unit.
     */
    public function testIsSiReturnsFalseForImperialUnit(): void
    {
        $du = CompoundUnit::parse('ft');

        $this->assertFalse($du->isSi());
    }

    /**
     * Test isSi returns false for mixed SI and Imperial units.
     */
    public function testIsSiReturnsFalseForMixedUnits(): void
    {
        $du = CompoundUnit::parse('kg*ft/s2');

        $this->assertFalse($du->isSi());
    }

    /**
     * Test isSi returns false for dimensionless unit.
     */
    public function testIsSiReturnsFalseForDimensionless(): void
    {
        $du = new CompoundUnit();

        $this->assertFalse($du->isSi());
    }

    #endregion

    #region Method isBase() tests.

    /**
     * Test isBase returns true for simple base unit.
     */
    public function testIsBaseReturnsTrueForSimpleBaseUnit(): void
    {
        $du = CompoundUnit::parse('m');

        $this->assertTrue($du->isBase());
    }

    /**
     * Test isBase returns true for base unit with exponent.
     */
    public function testIsBaseReturnsTrueForBaseUnitWithExponent(): void
    {
        $du = CompoundUnit::parse('m2');

        $this->assertTrue($du->isBase());
    }

    /**
     * Test isBase returns true for compound of base units.
     */
    public function testIsBaseReturnsTrueForCompoundBaseUnits(): void
    {
        // kg*m/s2 - all base units (kg, m, s are non-expandable).
        $du = CompoundUnit::parse('kg*m/s2');

        $this->assertTrue($du->isBase());
    }

    /**
     * Test isBase returns false for compound unit.
     */
    public function testIsBaseReturnsFalseForCompoundUnit(): void
    {
        // Newton can be expanded to kg*m/s2.
        $du = CompoundUnit::parse('N');

        $this->assertFalse($du->isBase());
    }

    /**
     * Test isBase returns true for dimensionless unit.
     */
    public function testIsBaseReturnsTrueForDimensionless(): void
    {
        $du = new CompoundUnit();

        $this->assertTrue($du->isBase());
    }

    #endregion

    #region Method hasPrefixes() tests.

    /**
     * Test hasPrefixes returns true when a unit term has a prefix.
     */
    public function testHasPrefixesReturnsTrueForPrefixedUnit(): void
    {
        $du = CompoundUnit::parse('km');

        $this->assertTrue($du->hasPrefixes());
    }

    /**
     * Test hasPrefixes returns true when at least one term has a prefix.
     */
    public function testHasPrefixesReturnsTrueWhenOneTermHasPrefix(): void
    {
        // km/s - km has a prefix, s does not.
        $du = CompoundUnit::parse('km/s');

        $this->assertTrue($du->hasPrefixes());
    }

    /**
     * Test hasPrefixes returns true for kg (the SI base mass unit uses a prefix).
     */
    public function testHasPrefixesReturnsTrueForKg(): void
    {
        $du = CompoundUnit::parse('kg');

        $this->assertTrue($du->hasPrefixes());
    }

    /**
     * Test hasPrefixes returns false when no unit terms have prefixes.
     */
    public function testHasPrefixesReturnsFalseForUnprefixedUnit(): void
    {
        $du = CompoundUnit::parse('m');

        $this->assertFalse($du->hasPrefixes());
    }

    /**
     * Test hasPrefixes returns false for compound unit without prefixes.
     */
    public function testHasPrefixesReturnsFalseForCompoundUnprefixedUnit(): void
    {
        $du = CompoundUnit::parse('m/s');

        $this->assertFalse($du->hasPrefixes());
    }

    /**
     * Test hasPrefixes returns false for empty (dimensionless) unit.
     */
    public function testHasPrefixesReturnsFalseForDimensionless(): void
    {
        $du = new CompoundUnit();

        $this->assertFalse($du->hasPrefixes());
    }

    #endregion

    #region Method isMergeable() tests.

    /**
     * Test isMergeable returns true when two terms share the same dimension.
     */
    public function testIsMergeableReturnsTrueForSameDimensionTerms(): void
    {
        // m and ft are both length (dimension 'L').
        $m = new UnitTerm('m');
        $ft = new UnitTerm('ft');
        $du = new CompoundUnit([$m, $ft]);

        $this->assertTrue($du->isMergeable());
    }

    /**
     * Test isMergeable returns false when all terms have different dimensions.
     */
    public function testIsMergeableReturnsFalseForDifferentDimensions(): void
    {
        // kg*m/s2 - mass, length, and time are all different dimensions.
        $du = CompoundUnit::parse('kg*m/s2');

        $this->assertFalse($du->isMergeable());
    }

    /**
     * Test isMergeable returns false for a single unit term.
     */
    public function testIsMergeableReturnsFalseForSingleTerm(): void
    {
        $du = CompoundUnit::parse('m');

        $this->assertFalse($du->isMergeable());
    }

    /**
     * Test isMergeable returns false for empty (dimensionless) unit.
     */
    public function testIsMergeableReturnsFalseForDimensionless(): void
    {
        $du = new CompoundUnit();

        $this->assertFalse($du->isMergeable());
    }

    #endregion

    #region Method siPreferred() tests.

    /**
     * Test siPreferred returns true for pure SI unit.
     */
    public function testSiPreferredForPureSiUnit(): void
    {
        $du = CompoundUnit::parse('kg');

        $this->assertTrue($du->siPreferred());
    }

    /**
     * Test siPreferred returns false for pure English unit.
     */
    public function testSiPreferredReturnsFalseForEnglishUnit(): void
    {
        $du = CompoundUnit::parse('lb');

        $this->assertFalse($du->siPreferred());
    }

    /**
     * Test siPreferred returns true for mixed SI and English units.
     */
    public function testSiPreferredReturnsTrueForMixedUnits(): void
    {
        // kg (SI) and ft (English) — has both, but at least one unambiguous SI unit.
        $du = CompoundUnit::parse('kg*ft');

        $this->assertTrue($du->siPreferred());
    }

    /**
     * Test siPreferred returns true for common base unit that is ambiguous.
     */
    public function testSiPreferredForAmbiguousBaseUnit(): void
    {
        // 's' is used with both SI and English systems, so it's in the common list.
        // With no English units and no unambiguous SI units, nEnglishUnits === 0 → true.
        $du = CompoundUnit::parse('s');

        $this->assertTrue($du->siPreferred());
    }

    /**
     * Test siPreferred returns false for English compound unit.
     */
    public function testSiPreferredReturnsFalseForEnglishCompound(): void
    {
        // lb*ft/s2 — lb and ft are English, s is ambiguous.
        $du = CompoundUnit::parse('lb*ft/s2');

        $this->assertFalse($du->siPreferred());
    }

    #endregion

    #region Method includesUnit() tests.

    /**
     * Test includesUnit returns true for a unit in the compound unit.
     */
    public function testIncludesUnitReturnsTrueForIncludedUnit(): void
    {
        $du = CompoundUnit::parse('kg*m/s2');
        $meter = UnitService::getBySymbol('m');
        $this->assertInstanceOf(Unit::class, $meter);

        $this->assertTrue($du->includesUnit($meter));
    }

    /**
     * Test includesUnit returns false for a unit not in the compound unit.
     */
    public function testIncludesUnitReturnsFalseForNonIncludedUnit(): void
    {
        $du = CompoundUnit::parse('kg*m/s2');
        $foot = UnitService::getBySymbol('ft');
        $this->assertInstanceOf(Unit::class, $foot);

        $this->assertFalse($du->includesUnit($foot));
    }

    /**
     * Test includesUnit with prefixed unit term finds the base unit.
     */
    public function testIncludesUnitWithPrefixedTerm(): void
    {
        $du = CompoundUnit::parse('km');
        $meter = UnitService::getBySymbol('m');
        $this->assertInstanceOf(Unit::class, $meter);

        $this->assertTrue($du->includesUnit($meter));
    }

    /**
     * Test includesUnit on a single-term compound unit.
     */
    public function testIncludesUnitOnSingleTerm(): void
    {
        $du = CompoundUnit::parse('ft');
        $foot = UnitService::getBySymbol('ft');
        $this->assertInstanceOf(Unit::class, $foot);

        $this->assertTrue($du->includesUnit($foot));
    }

    #endregion

    #region Method includesMetricUnit() tests.

    /**
     * Test includesMetricUnit() returns true when any term uses an SI or Metric unit.
     */
    public function testIncludesMetricUnitReturnsTrueForMetricUnits(): void
    {
        foreach (['km', 'L', 'ft/m', 'kW*h'] as $symbol) {
            $this->assertTrue(CompoundUnit::parse($symbol)->includesMetricUnit(), $symbol);
        }
    }

    /**
     * Test includesMetricUnit() returns false for English units, SI base units shared with English units, and units
     * accepted for use with SI.
     */
    public function testIncludesMetricUnitReturnsFalseForNonMetricUnits(): void
    {
        foreach (['ft', 's', 'mol', 'ft*s', 'lb/s', 'deg', 'h'] as $symbol) {
            $this->assertFalse(CompoundUnit::parse($symbol)->includesMetricUnit(), $symbol);
        }
    }

    #endregion
}
