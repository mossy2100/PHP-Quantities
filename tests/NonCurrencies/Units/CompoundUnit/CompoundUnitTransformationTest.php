<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\CompoundUnit;

use Error;
use Mossy2100\Quantities\Quantity;
use Mossy2100\Quantities\Units\CompoundUnit;
use Mossy2100\Quantities\Units\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the transformation methods of CompoundUnit.
 */
#[CoversClass(CompoundUnit::class)]
class CompoundUnitTransformationTest extends TestCase
{
    #region Method toSiBase() tests.

    public function testToSiSimpleSiUnit(): void
    {
        // meter is already SI base unit
        $du = CompoundUnit::parse('m');
        $si = $du->toSiBase();

        $this->assertSame('m', $si->format(true));
        $this->assertSame('L', $si->dimension);
    }

    public function testToSiPrefixedSiUnit(): void
    {
        // kilometer should convert to meter (SI base)
        $du = CompoundUnit::parse('km');
        $si = $du->toSiBase();

        $this->assertSame('m', $si->format(true));
        $this->assertSame('L', $si->dimension);
    }

    public function testToSiNonSiUnit(): void
    {
        // foot should convert to meter
        $du = CompoundUnit::parse('ft');
        $si = $du->toSiBase();

        $this->assertSame('m', $si->format(true));
        $this->assertSame('L', $si->dimension);
    }

    public function testToSiUnitWithExponent(): void
    {
        // m² stays as m²
        $du = CompoundUnit::parse('m2');
        $si = $du->toSiBase();

        $this->assertSame('m2', $si->format(true));
        $this->assertSame('L2', $si->dimension);
    }

    public function testToSiNonSiUnitWithExponent(): void
    {
        // ft² should convert to m²
        $du = CompoundUnit::parse('ft2');
        $si = $du->toSiBase();

        $this->assertSame('m2', $si->format(true));
        $this->assertSame('L2', $si->dimension);
    }

    public function testToSiPrefixedUnitWithExponent(): void
    {
        // km² should convert to m²
        $du = CompoundUnit::parse('km2');
        $si = $du->toSiBase();

        $this->assertSame('m2', $si->format(true));
        $this->assertSame('L2', $si->dimension);
    }

    public function testToSiCompoundUnit(): void
    {
        // m/s is already SI
        $du = CompoundUnit::parse('m/s');
        $si = $du->toSiBase();

        $this->assertSame('m/s', $si->format(true));
        $this->assertSame('LT-1', $si->dimension);
    }

    public function testToSiNonSiCompoundUnit(): void
    {
        // ft/s should convert to m*s⁻¹
        $du = CompoundUnit::parse('ft/s');
        $si = $du->toSiBase();

        $this->assertSame('m/s', $si->format(true));
        $this->assertSame('LT-1', $si->dimension);
    }

    public function testToSiForceUnit(): void
    {
        // kg*m/s² (force) should stay as kg*m*s⁻²
        $du = CompoundUnit::parse('kg*m/s2');
        $si = $du->toSiBase();

        $this->assertSame('kg*m/s2', $si->format(true));
        $this->assertSame('MLT-2', $si->dimension);
    }

    public function testToSiNonSiForceUnit(): void
    {
        // lb*ft/s² should convert to kg*m*s⁻²
        $du = CompoundUnit::parse('lb*ft/s2');
        $si = $du->toSiBase();

        $this->assertSame('kg*m/s2', $si->format(true));
        $this->assertSame('MLT-2', $si->dimension);
    }

    public function testToSiNamedUnitNewton(): void
    {
        // Newton (N) has dimension MLT-2, should convert to kg*m*s⁻²
        $du = CompoundUnit::parse('N');
        $si = $du->toSiBase();

        $this->assertSame('kg*m/s2', $si->format(true));
        $this->assertSame('MLT-2', $si->dimension);
    }

    public function testToSiNamedUnitJoule(): void
    {
        // Joule (J) has dimension ML²T⁻², should convert to kg*m²*s⁻²
        $du = CompoundUnit::parse('J');
        $si = $du->toSiBase();

        $this->assertSame('kg*m2/s2', $si->format(true));
        $this->assertSame('ML2T-2', $si->dimension);
    }

    public function testToSiNamedUnitWatt(): void
    {
        // Watt (W) has dimension ML²T⁻³, should convert to kg*m²*s⁻³
        $du = CompoundUnit::parse('W');
        $si = $du->toSiBase();

        $this->assertSame('kg*m2/s3', $si->format(true));
        $this->assertSame('ML2T-3', $si->dimension);
    }

    public function testToSiNamedUnitPascal(): void
    {
        // Pascal (Pa) has dimension ML⁻¹T⁻², should convert to kg*m⁻¹*s⁻²
        $du = CompoundUnit::parse('Pa');
        $si = $du->toSiBase();

        $this->assertSame('kg/(m*s2)', $si->format(true));
        $this->assertSame('ML-1T-2', $si->dimension);
    }

    public function testToSiNamedUnitHertz(): void
    {
        // Hertz (Hz) has dimension T⁻¹, should convert to s⁻¹
        $du = CompoundUnit::parse('Hz');
        $si = $du->toSiBase();

        $this->assertSame('s-1', $si->format(true));
        $this->assertSame('T-1', $si->dimension);
    }

    public function testToSiPrefixedNamedUnit(): void
    {
        // kN (kilonewton) has dimension MLT-2, should convert to kg*m*s⁻²
        $du = CompoundUnit::parse('kN');
        $si = $du->toSiBase();

        $this->assertSame('kg*m/s2', $si->format(true));
        $this->assertSame('MLT-2', $si->dimension);
    }

    public function testToSiMassUnit(): void
    {
        // gram should convert to kg (SI base unit for mass has 'k' prefix)
        $du = CompoundUnit::parse('g');
        $si = $du->toSiBase();

        $this->assertSame('kg', $si->format(true));
        $this->assertSame('M', $si->dimension);
    }

    public function testToSiNonSiMassUnit(): void
    {
        // pound should convert to kg
        $du = CompoundUnit::parse('lb');
        $si = $du->toSiBase();

        $this->assertSame('kg', $si->format(true));
        $this->assertSame('M', $si->dimension);
    }

    public function testToSiEmptyUnit(): void
    {
        // Empty (dimensionless) unit should stay empty
        $du = new CompoundUnit();
        $si = $du->toSiBase();

        $this->assertSame('', $si->format(true));
        $this->assertSame('', $si->dimension);
    }

    public function testToSiPreservesDimension(): void
    {
        // The dimension should be the same before and after toSi()
        $du = CompoundUnit::parse('ft*lb/s2');
        $si = $du->toSiBase();

        $this->assertSame($du->dimension, $si->dimension);
    }

    public function testToSiDoesNotModifyOriginal(): void
    {
        $du = CompoundUnit::parse('ft');
        $si = $du->toSiBase();

        $this->assertSame('ft', $du->format(true));
        $this->assertSame('m', $si->format(true));
    }

    public function testToSiComplexMixedUnit(): void
    {
        // mph (miles per hour) components: mi/h - both non-SI
        // Dimension is LT⁻¹, should become m/s
        $du = CompoundUnit::parse('mi/h');
        $si = $du->toSiBase();

        $this->assertSame('m/s', $si->format(true));
        $this->assertSame('LT-1', $si->dimension);
    }

    public function testToSiEnergyPerTime(): void
    {
        // J/s = W, dimension ML²T⁻³
        $du = CompoundUnit::parse('J/s');
        $si = $du->toSiBase();

        $this->assertSame('kg*m2/s3', $si->format(true));
        $this->assertSame('ML2T-3', $si->dimension);
    }

    public function testToSiAcceleration(): void
    {
        // ft/s² should become m/s²
        $du = CompoundUnit::parse('ft/s2');
        $si = $du->toSiBase();

        $this->assertSame('m/s2', $si->format(true));
        $this->assertSame('LT-2', $si->dimension);
    }

    public function testToSiMixedExpandableAndBase(): void
    {
        // N*s mixes an expandable term (N → kg*m/s2) with a base term (s).
        // Result: kg*m/s2 * s = kg*m/s. Verifies the internal expansion correctly handles
        // compounds where some terms expand and others don't.
        $du = CompoundUnit::parse('N*s');
        $si = $du->toSiBase();

        $this->assertSame('kg*m/s', $si->format(true));
        $this->assertSame('MLT-1', $si->dimension);
    }

    #endregion

    #region Method __clone() tests.

    /**
     * Test clone produces a separate instance with the same value.
     */
    public function testCloneProducesSeparateInstanceWithSameValue(): void
    {
        $original = CompoundUnit::parse('kg*m/s2');
        $cloned = clone $original;

        $this->assertTrue($original->equal($cloned));
        $this->assertNotSame($original, $cloned);
    }

    /**
     * Test clone deep clones unit terms.
     */
    public function testCloneDeepClonesUnitTerms(): void
    {
        $original = CompoundUnit::parse('m/s');
        $cloned = clone $original;

        // Unit terms should be different instances.
        foreach ($original->unitTerms as $symbol => $unitTerm) {
            $this->assertNotSame($unitTerm, $cloned->unitTerms[$symbol]);
        }
    }

    /**
     * Test that operations returning a new CompoundUnit don't mutate the original.
     *
     * CompoundUnit is externally immutable — mutators are private and arithmetic methods like mul() return new
     * instances. This test exercises that guarantee via the public API.
     */
    public function testMulDoesNotMutateOriginal(): void
    {
        $original = CompoundUnit::parse('m/s');
        $other = CompoundUnit::parse('kg');

        $result = $original->mul($other);

        // Original should be unchanged.
        $this->assertCount(2, $original->unitTerms);
        $this->assertSame('m/s', $original->format(true));
        // Result has one more term.
        $this->assertCount(3, $result->unitTerms);
    }

    /**
     * Test that addUnitTerm cannot be called externally. This pins the private visibility of the mutator, which is
     * load-bearing for the deep-immutability guarantee.
     */
    public function testAddUnitTermIsPrivate(): void
    {
        $cu = CompoundUnit::parse('m');

        $this->expectException(Error::class);
        $this->expectExceptionMessage('private method');

        // @phpstan-ignore method.private
        $cu->addUnitTerm(new UnitTerm('s'));
    }

    /**
     * Test clone of empty compound unit.
     */
    public function testCloneOfEmptyCompoundUnit(): void
    {
        $original = new CompoundUnit();
        $cloned = clone $original;

        $this->assertTrue($original->equal($cloned));
        $this->assertNotSame($original, $cloned);
        $this->assertEmpty($cloned->unitTerms);
    }

    #endregion

    #region Method removePrefixes() tests.

    /**
     * Test removePrefixes removes a simple prefix.
     */
    public function testRemovePrefixesRemovesSimplePrefix(): void
    {
        $du = CompoundUnit::parse('km');
        $result = $du->removePrefixes();

        $this->assertSame('m', $result->format(true));
    }

    /**
     * Test removePrefixes removes prefixes from all terms.
     */
    public function testRemovePrefixesRemovesFromAllTerms(): void
    {
        // km/ms - both terms have prefixes.
        $du = CompoundUnit::parse('km/ms');
        $result = $du->removePrefixes();

        $this->assertSame('m/s', $result->format(true));
    }

    /**
     * Test removePrefixes does not modify original.
     */
    public function testRemovePrefixesDoesNotModifyOriginal(): void
    {
        $du = CompoundUnit::parse('km');
        $result = $du->removePrefixes();

        $this->assertSame('km', $du->format(true));
        $this->assertSame('m', $result->format(true));
    }

    /**
     * Test removePrefixes on unit without prefixes.
     */
    public function testRemovePrefixesOnUnprefixedUnit(): void
    {
        $du = CompoundUnit::parse('m/s');
        $result = $du->removePrefixes();

        $this->assertSame('m/s', $result->format(true));
    }

    /**
     * Test removePrefixes preserves exponents.
     */
    public function testRemovePrefixesPreservesExponents(): void
    {
        $du = CompoundUnit::parse('km2');
        $result = $du->removePrefixes();

        $this->assertSame('m2', $result->format(true));
    }

    /**
     * Test removePrefixes on empty compound unit.
     */
    public function testRemovePrefixesOnEmptyCompoundUnit(): void
    {
        $du = new CompoundUnit();
        $result = $du->removePrefixes();

        $this->assertSame('', $result->format(true));
    }

    #endregion

    #region Method toEnglishBase() tests.

    /**
     * Test toEnglishBase for length dimension.
     */
    public function testToEnglishBaseForLength(): void
    {
        $du = CompoundUnit::parse('m');
        $result = $du->toEnglishBase();

        $this->assertSame('ft', $result->asciiSymbol);
    }

    /**
     * Test toEnglishBase for mass dimension.
     */
    public function testToEnglishBaseForMass(): void
    {
        $du = CompoundUnit::parse('kg');
        $result = $du->toEnglishBase();

        $this->assertSame('lb', $result->asciiSymbol);
    }

    /**
     * Test toEnglishBase for compound dimension.
     */
    public function testToEnglishBaseForCompoundDimension(): void
    {
        // Force: MLT-2. English base should be lb*ft/s2.
        $du = CompoundUnit::parse('N');
        $result = $du->toEnglishBase();

        $this->assertSame('lb*ft/s2', $result->asciiSymbol);
    }

    /**
     * Test toEnglishBase for time dimension falls back to SI base.
     */
    public function testToEnglishBaseForTimeFallsBackToSi(): void
    {
        // Time has no English base unit, falls back to SI (seconds).
        $du = CompoundUnit::parse('h');
        $result = $du->toEnglishBase();

        $this->assertSame('s', $result->asciiSymbol);
    }

    #endregion

    #region Method merge() tests.

    /**
     * Test merge combines compatible unit terms.
     */
    public function testMergeCombinesCompatibleUnits(): void
    {
        // m*ft — both are length, should merge to a single length unit.
        $du = CompoundUnit::parse('m*ft');
        $result = $du->merge();

        $this->assertInstanceOf(Quantity::class, $result);
        // m*ft: ft → m conversion is 0.3048, so 1 m*ft = 0.3048 m2.
        $this->assertSame('m2', $result->compoundUnit->asciiSymbol);
        $this->assertEqualsWithDelta(0.3048, $result->value, 1e-4);
    }

    /**
     * Test merge returns value of 1.0 when no conversion is needed.
     */
    public function testMergeWithNoConversionNeeded(): void
    {
        // m*m — same unit, should merge to m2 with value 1.0.
        $du = CompoundUnit::parse('m*m');

        // Note: m*m should already combine in the parser to m2.
        // Let's use a unit that doesn't auto-combine.
        $result = $du->merge();

        $this->assertInstanceOf(Quantity::class, $result);
        $this->assertSame(1.0, $result->value);
    }

    /**
     * Test merge on a unit with no mergeable terms returns unchanged.
     */
    public function testMergeWithNoMergeableTerms(): void
    {
        $du = CompoundUnit::parse('kg*m/s2');
        $result = $du->merge();

        $this->assertInstanceOf(Quantity::class, $result);
        $this->assertSame(1.0, $result->value);
        $this->assertSame('kg*m/s2', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test merge with different units of the same dimension in a compound.
     */
    public function testMergeWithDifferentUnitsOfSameDimension(): void
    {
        // kg*lb — both are mass, should merge.
        $du = CompoundUnit::parse('kg*lb');
        $result = $du->merge();

        $this->assertInstanceOf(Quantity::class, $result);
        $this->assertSame('kg2', $result->compoundUnit->asciiSymbol);
        // 1 lb ≈ 0.45359 kg, so 1 kg*lb ≈ 0.45359 kg2.
        $this->assertEqualsWithDelta(0.45359, $result->value, 1e-4);
    }

    #endregion
}
