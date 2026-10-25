<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\CompoundUnit;

use Mossy2100\Quantities\Units\CompoundUnit;
use Mossy2100\Quantities\Units\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the properties of CompoundUnit.
 */
#[CoversClass(CompoundUnit::class)]
class CompoundUnitPropertiesTest extends TestCase
{
    #region Property $dimension tests.

    public function testDimensionSimpleUnit(): void
    {
        $du = CompoundUnit::parse('m');
        $this->assertSame('L', $du->dimension);
    }

    public function testDimensionUnitWithExponent(): void
    {
        $du = CompoundUnit::parse('m3');
        $this->assertSame('L3', $du->dimension);
    }

    public function testDimensionCompoundUnit(): void
    {
        $du = CompoundUnit::parse('m/s');
        $this->assertSame('LT-1', $du->dimension);
    }

    public function testDimensionEmptyUnit(): void
    {
        $du = new CompoundUnit();
        $this->assertSame('', $du->dimension);
    }

    public function testDimensionVelocity(): void
    {
        // Velocity: length/time
        $du = CompoundUnit::parse('km/s');
        $this->assertSame('LT-1', $du->dimension);
    }

    public function testDimensionAcceleration(): void
    {
        // Acceleration: length/time²
        $du = CompoundUnit::parse('m/s2');
        $this->assertSame('LT-2', $du->dimension);
    }

    public function testDimensionForce(): void
    {
        // Force (Newton): mass⋅length/time²
        $du = CompoundUnit::parse('kg*m/s2');
        $this->assertSame('MLT-2', $du->dimension);
    }

    public function testDimensionEnergy(): void
    {
        // Energy (Joule): mass⋅length²/time²
        $du = CompoundUnit::parse('kg*m2/s2');
        $this->assertSame('ML2T-2', $du->dimension);
    }

    public function testDimensionCancellingTermsYieldsDimensionless(): void
    {
        // N*m = J, so N*m/J is dimensionless. The M, L2, and T-2 contributions
        // from N*m are cancelled by J^-1's M-1, L-2, T2 contributions inside getDimension().
        $du = CompoundUnit::parse('N*m/J');
        $this->assertSame('', $du->dimension);
    }

    public function testDimensionPartialCancellationLeavesRemainder(): void
    {
        // W has dimension ML2T-3; W/m2 cancels the L2, leaving MT-3 (irradiance).
        $du = CompoundUnit::parse('W/m2');
        $this->assertSame('MT-3', $du->dimension);
    }

    #endregion

    #region Property $multiplier tests.

    /**
     * Test multiplier property for a single unprefixed unit.
     */
    public function testMultiplierPropertyForSingleUnit(): void
    {
        $du = CompoundUnit::parse('m');

        $this->assertSame(1.0, $du->multiplier);
    }

    /**
     * Test multiplier property for a prefixed unit.
     */
    public function testMultiplierPropertyForPrefixedUnit(): void
    {
        $du = CompoundUnit::parse('km');

        $this->assertSame(1000.0, $du->multiplier);
    }

    /**
     * Test multiplier property for a prefixed unit with exponent.
     */
    public function testMultiplierPropertyForPrefixedUnitWithExponent(): void
    {
        // km2 => (1000)^2 = 1e6
        $du = CompoundUnit::parse('km2');

        $this->assertSame(1e6, $du->multiplier);
    }

    /**
     * Test multiplier property for compound unit with multiple prefixes.
     */
    public function testMultiplierPropertyForCompoundUnit(): void
    {
        // kg*km => 1000 * 1000 = 1e6 (kg prefix = 1000, km prefix = 1000)
        $du = CompoundUnit::parse('kg*km');

        $this->assertSame(1e6, $du->multiplier);
    }

    /**
     * Test multiplier property for empty CompoundUnit.
     */
    public function testMultiplierPropertyForEmptyUnit(): void
    {
        $du = new CompoundUnit();

        $this->assertSame(1.0, $du->multiplier);
    }

    #endregion

    #region Property $firstUnitTerm tests.

    /**
     * Test firstUnitTerm property returns first unit term.
     */
    public function testFirstUnitTermPropertyReturnsSingleTerm(): void
    {
        $du = CompoundUnit::parse('m');

        $this->assertInstanceOf(UnitTerm::class, $du->firstUnitTerm);
        $this->assertSame('meter', $du->firstUnitTerm->unit->name);
    }

    /**
     * Test firstUnitTerm property returns the first term (in input order) in a compound unit
     * with multiple terms.
     */
    public function testFirstUnitTermPropertyReturnsFirstInCompound(): void
    {
        $du = CompoundUnit::parse('kg*m/s2');

        $this->assertInstanceOf(UnitTerm::class, $du->firstUnitTerm);
        $this->assertSame('kg', $du->firstUnitTerm->asciiSymbol);
    }

    /**
     * Test firstUnitTerm property returns null for empty CompoundUnit.
     */
    public function testFirstUnitTermPropertyReturnsNullForEmpty(): void
    {
        $du = new CompoundUnit();

        $this->assertNull($du->firstUnitTerm);
    }

    #endregion

    #region Property $quantityType tests.

    /**
     * Test quantityType property returns QuantityType for a registered dimension.
     */
    public function testQuantityTypePropertyReturnsQuantityType(): void
    {
        $du = CompoundUnit::parse('m');

        $this->assertNotNull($du->quantityType);
        $this->assertSame('length', $du->quantityType->name);
    }

    /**
     * Test quantityType property returns null for an unregistered dimension.
     */
    public function testQuantityTypePropertyReturnsNullForUnregisteredDimension(): void
    {
        // L5T3 doesn't correspond to any registered quantity type.
        $du = CompoundUnit::parse('m5*s3');

        $this->assertNull($du->quantityType);
    }

    #endregion
}
