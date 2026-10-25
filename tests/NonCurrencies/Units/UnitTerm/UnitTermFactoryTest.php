<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\UnitTerm;

use DomainException;
use Mossy2100\Core\Exceptions\FormatException;
use Mossy2100\Quantities\Exceptions\UnknownUnitException;
use Mossy2100\Quantities\Services\UnitService;
use Mossy2100\Quantities\Units\Unit;
use Mossy2100\Quantities\Units\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the factory methods of UnitTerm.
 */
#[CoversClass(UnitTerm::class)]
final class UnitTermFactoryTest extends TestCase
{
    #region Method parse() tests.

    /**
     * Test parse with simple unit.
     */
    public function testParseSimpleUnit(): void
    {
        $term = UnitTerm::parse('m');

        $this->assertSame('meter', $term->unit->name);
        $this->assertNull($term->prefix);
        $this->assertSame(1, $term->exponent);
    }

    /**
     * Test parse with prefixed unit.
     */
    public function testParsePrefixedUnit(): void
    {
        $term = UnitTerm::parse('km');

        $this->assertSame('meter', $term->unit->name);
        $this->assertSame('k', $term->prefix?->asciiSymbol);
        $this->assertSame(1, $term->exponent);
    }

    /**
     * Test parse with ASCII exponent.
     */
    public function testParseWithAsciiExponent(): void
    {
        $term = UnitTerm::parse('m2');

        $this->assertSame('meter', $term->unit->name);
        $this->assertSame(2, $term->exponent);
    }

    /**
     * Test parse with negative ASCII exponent.
     */
    public function testParseWithNegativeAsciiExponent(): void
    {
        $term = UnitTerm::parse('s-2');

        $this->assertSame('second', $term->unit->name);
        $this->assertSame(-2, $term->exponent);
    }

    /**
     * Test parse with superscript exponent.
     */
    public function testParseWithSuperscriptExponent(): void
    {
        $term = UnitTerm::parse('m²');

        $this->assertSame('meter', $term->unit->name);
        $this->assertSame(2, $term->exponent);
    }

    /**
     * Test parse with negative superscript exponent.
     */
    public function testParseWithNegativeSuperscriptExponent(): void
    {
        $term = UnitTerm::parse('s⁻²');

        $this->assertSame('second', $term->unit->name);
        $this->assertSame(-2, $term->exponent);
    }

    /**
     * Test parse with prefix and exponent.
     */
    public function testParseWithPrefixAndExponent(): void
    {
        $term = UnitTerm::parse('km2');

        $this->assertSame('meter', $term->unit->name);
        $this->assertSame('k', $term->prefix?->asciiSymbol);
        $this->assertSame(2, $term->exponent);
    }

    /**
     * Test parse throws for invalid format.
     */
    public function testParseThrowsForInvalidFormat(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid unit');

        UnitTerm::parse('123');
    }

    /**
     * Test parse throws for unknown unit.
     */
    public function testParseThrowsForUnknownUnit(): void
    {
        $this->expectException(UnknownUnitException::class);
        $this->expectExceptionMessage("Unknown unit: 'xyz'");

        UnitTerm::parse('xyz');
    }

    /**
     * Test parse with empty string returns dimensionless scalar unit.
     */
    public function testParseEmptyStringReturnsDimensionlessUnit(): void
    {
        $term = UnitTerm::parse('');

        $this->assertSame('scalar', $term->unit->name);
        $this->assertNull($term->prefix);
        $this->assertSame(1, $term->exponent);
    }

    /**
     * Test parse throws for ASCII minus with superscript digit.
     */
    public function testParseThrowsForAsciiMinusWithSuperscriptDigit(): void
    {
        $this->expectException(FormatException::class);

        // ASCII minus with superscript digit is not valid.
        UnitTerm::parse('m-²');
    }

    /**
     * Test parse throws for exponent of zero (rejected by the constructor).
     */
    public function testParseThrowsForExponentZero(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid exponent: 0. Cannot be zero.');

        UnitTerm::parse('m0');
    }

    /**
     * Test parse throws for an explicit exponent of 1 — write 'm' instead of 'm1'.
     */
    public function testParseThrowsForExponentOne(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid unit term symbol: 'm1'. An exponent of 1 must be omitted.");

        UnitTerm::parse('m1');
    }

    /**
     * Test parse throws when a unit with a non-letter symbol is given an exponent.
     */
    public function testParseThrowsForNonLetterSymbolWithExponent(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Units containing non-letters cannot have exponents');

        UnitTerm::parse('%2');
    }

    /**
     * Test parse throws when a dimensionless unit is given an exponent.
     */
    public function testParseThrowsForDimensionlessUnitWithExponent(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Dimensionless units cannot have exponents');

        UnitTerm::parse('ppm2');
    }

    #endregion

    #region Method toUnitTerm() tests.

    /**
     * Test toUnitTerm with UnitTerm returns same instance.
     */
    public function testToUnitTermWithUnitTermReturnsSame(): void
    {
        $term = new UnitTerm('m', 'k', 2);
        $result = UnitTerm::toUnitTerm($term);

        $this->assertSame($term, $result);
    }

    /**
     * Test toUnitTerm with string parses it.
     */
    public function testToUnitTermWithStringParsesIt(): void
    {
        $result = UnitTerm::toUnitTerm('km2');

        $this->assertInstanceOf(UnitTerm::class, $result);
        $this->assertSame('meter', $result->unit->name);
        $this->assertSame('k', $result->prefix?->asciiSymbol);
        $this->assertSame(2, $result->exponent);
    }

    /**
     * Test toUnitTerm with Unit creates UnitTerm.
     */
    public function testToUnitTermWithUnitCreatesUnitTerm(): void
    {
        $unit = UnitService::getBySymbol('m');

        $this->assertInstanceOf(Unit::class, $unit);
        $result = UnitTerm::toUnitTerm($unit);

        $this->assertInstanceOf(UnitTerm::class, $result);
        $this->assertSame('meter', $result->unit->name);
        $this->assertNull($result->prefix);
        $this->assertSame(1, $result->exponent);
    }

    #endregion
}
