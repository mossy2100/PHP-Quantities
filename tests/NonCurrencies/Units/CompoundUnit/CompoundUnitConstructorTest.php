<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\CompoundUnit;

use Mossy2100\Quantities\Services\UnitService;
use Mossy2100\Quantities\Units\CompoundUnit;
use Mossy2100\Quantities\Units\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the constructor of CompoundUnit.
 */
#[CoversClass(CompoundUnit::class)]
class CompoundUnitConstructorTest extends TestCase
{
    #region Method __construct() tests.

    public function testConstructorWithNull(): void
    {
        $du = new CompoundUnit(null);
        $this->assertEmpty($du->unitTerms);
        $this->assertSame('', $du->format(true));
    }

    public function testConstructorWithNoArgument(): void
    {
        $du = new CompoundUnit();
        $this->assertEmpty($du->unitTerms);
    }

    public function testConstructorWithUnit(): void
    {
        $meter = UnitService::getBySymbol('m');
        $du = new CompoundUnit($meter);

        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('m', $du->format(true));
    }

    public function testConstructorWithUnitTerm(): void
    {
        $unitTerm = new UnitTerm('m', 'k', 2);
        $du = new CompoundUnit($unitTerm);

        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('km2', $du->format(true));
    }

    public function testConstructorWithArrayOfUnitTerms(): void
    {
        // kg is g with prefix k
        $kg = new UnitTerm('g', 'k');
        $m = new UnitTerm('m');
        $s = new UnitTerm('s', null, -2);

        $du = new CompoundUnit([$kg, $m, $s]);

        $this->assertCount(3, $du->unitTerms);
        // Sorted by dimension order: M, L, T.
        $this->assertSame('kg*m/s2', $du->format(true));
    }

    /**
     * The constructor builds the unit term list from the input array. Same-unit terms are combined (exponents
     * summed), zero-exponent terms are dropped, and differently-prefixed terms of the same base unit are kept
     * separate. These tests exercise those combination rules through the public constructor API.
     */
    public function testConstructorWithSingleTerm(): void
    {
        $cu = new CompoundUnit([new UnitTerm('m')]);

        $this->assertCount(1, $cu->unitTerms);
        $this->assertSame('m', $cu->format(true));
    }

    public function testConstructorCombinesSameUnitExponents(): void
    {
        $cu = new CompoundUnit([new UnitTerm('m', null, 2), new UnitTerm('m', null, 3)]);

        $this->assertCount(1, $cu->unitTerms);
        $this->assertSame('m5', $cu->format(true));
    }

    public function testConstructorCombinesWithDifferentExponents(): void
    {
        $cu = new CompoundUnit([new UnitTerm('m', null, 3), new UnitTerm('m', null, -1)]);

        $this->assertCount(1, $cu->unitTerms);
        $this->assertSame('m2', $cu->format(true));
    }

    public function testConstructorDropsTermsWhenExponentsCancel(): void
    {
        $cu = new CompoundUnit([new UnitTerm('m', null, 2), new UnitTerm('m', null, -2)]);

        $this->assertCount(0, $cu->unitTerms);
        $this->assertSame('', $cu->format(true));
    }

    public function testConstructorCombinesSamePrefix(): void
    {
        $cu = new CompoundUnit([new UnitTerm('m', 'k', 2), new UnitTerm('m', 'k', 1)]);

        $this->assertCount(1, $cu->unitTerms);
        $this->assertSame('km3', $cu->format(true));
    }

    public function testConstructorTreatsDifferentPrefixesSeparately(): void
    {
        // km and m should be treated as different unit terms (different unexponentiated symbols).
        $cu = new CompoundUnit([new UnitTerm('m', 'k'), new UnitTerm('m')]);

        $this->assertCount(2, $cu->unitTerms);
    }

    #endregion

    #region Sorting tests.

    public function testSortingByDimensionOrderMassLengthTime(): void
    {
        // Add in reverse dimension order: time, length, mass.
        $s = new UnitTerm('s');
        $m = new UnitTerm('m');
        $kg = new UnitTerm('g', 'k');

        $du = new CompoundUnit([$s, $m, $kg]);

        // Sorted by dimension order: M, L, T.
        $this->assertSame('kg*m*s', $du->format(true));
    }

    public function testSortingMixedExponents(): void
    {
        // kg⋅m⋅s⁻² - force units.
        $s = new UnitTerm('s', null, -2);
        $m = new UnitTerm('m');
        $kg = new UnitTerm('g', 'k');

        $du = new CompoundUnit([$s, $m, $kg]);

        // Sorted by dimension order: M, L, T.
        $this->assertSame('kg*m/s2', $du->format(true));
    }

    /**
     * Test sorting puts complex dimensions (more dimension terms) before simpler ones.
     */
    public function testSortingComplexDimensionBeforeSimple(): void
    {
        // N has dimension MLT-2 (3 terms), s has dimension T (1 term).
        // The more complex dimension should sort first.
        $s = new UnitTerm('s');
        $n = new UnitTerm('N');

        $du = new CompoundUnit([$s, $n]);

        $this->assertSame('N*s', $du->format(true));
    }

    /**
     * Test sorting by exponent when dimension letters are the same.
     *
     * Pa (ML⁻¹T⁻²) and J (ML²T⁻²) have the same dimension letters {M, L, T}
     * but different L exponents. Higher exponents sort first.
     */
    public function testSortingSameLettersDifferentExponents(): void
    {
        // J has dimension ML2T-2, Pa has dimension ML-1T-2.
        // Same letters, same count. The L exponent differs: J has 2, Pa has -1.
        // Higher exponent sorts first, so J should come before Pa.
        $pa = new UnitTerm('Pa');
        $j = new UnitTerm('J');

        $du = new CompoundUnit([$pa, $j]);

        $this->assertSame('J*Pa', $du->format(true));
    }

    #endregion

    #region Edge case tests.

    public function testCombiningSameUnitsViaMultiplication(): void
    {
        // m * m should give m²
        $du = CompoundUnit::parse('m*m');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('m2', $du->format(true));
    }

    public function testCombiningSameUnitsViaDivision(): void
    {
        // m / m should give empty (dimensionless)
        $du = CompoundUnit::parse('m/m');
        $this->assertCount(0, $du->unitTerms);
        $this->assertSame('', $du->format(true));
    }

    public function testComplexCombination(): void
    {
        // m³ / m should give m²
        $du = CompoundUnit::parse('m3/m');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('m2', $du->format(true));
    }

    public function testCombiningSamePrefixedUnitsViaMultiplication(): void
    {
        // km * km should give km²
        $du = CompoundUnit::parse('km*km');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('km2', $du->format(true));
    }

    public function testCombiningSamePrefixedUnitsViaDivision(): void
    {
        // km / km should give empty (dimensionless)
        $du = CompoundUnit::parse('km/km');
        $this->assertCount(0, $du->unitTerms);
        $this->assertSame('', $du->format(true));
    }

    public function testComplexPrefixedCombination(): void
    {
        // km³ / km should give km²
        $du = CompoundUnit::parse('km3/km');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('km2', $du->format(true));
    }

    #endregion
}
