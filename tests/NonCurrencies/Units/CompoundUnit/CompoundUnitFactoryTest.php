<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\CompoundUnit;

use Mossy2100\Core\Exceptions\FormatException;
use Mossy2100\Quantities\Exceptions\UnknownUnitException;
use Mossy2100\Quantities\Services\UnitService;
use Mossy2100\Quantities\Units\CompoundUnit;
use Mossy2100\Quantities\Units\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the factory methods of CompoundUnit.
 */
#[CoversClass(CompoundUnit::class)]
class CompoundUnitFactoryTest extends TestCase
{
    #region Method parse() tests.

    public function testParseEmptyStringReturnsDimensionless(): void
    {
        $du = CompoundUnit::parse('');
        $this->assertEmpty($du->unitTerms);
        $this->assertSame('', $du->dimension);
        $this->assertSame('', $du->format(true));
    }

    public function testParseSimpleUnit(): void
    {
        $du = CompoundUnit::parse('m');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('m', $du->format(true));
    }

    public function testParseUnitWithPrefix(): void
    {
        $du = CompoundUnit::parse('km');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('km', $du->format(true));
    }

    public function testParseUnitWithExponent(): void
    {
        $du = CompoundUnit::parse('m2');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('m2', $du->format(true));
    }

    public function testParseCompoundUnitWithAsterisk(): void
    {
        $du = CompoundUnit::parse('kg*m');
        $this->assertCount(2, $du->unitTerms);
        // Sorted by dimension order.
        $this->assertSame('kg*m', $du->format(true));
    }

    public function testParseCompoundUnitWithMiddot(): void
    {
        $du = CompoundUnit::parse('kg·m');
        $this->assertCount(2, $du->unitTerms);
        $this->assertSame('kg*m', $du->format(true));
    }

    public function testParseCompoundUnitWithDot(): void
    {
        $du = CompoundUnit::parse('kg.m');
        $this->assertCount(2, $du->unitTerms);
        $this->assertSame('kg*m', $du->format(true));
    }

    public function testParseCompoundUnitWithDivision(): void
    {
        $du = CompoundUnit::parse('m/s');
        $this->assertCount(2, $du->unitTerms);
        $this->assertSame('m/s', $du->format(true));
    }

    public function testParseComplexUnit(): void
    {
        $du = CompoundUnit::parse('kg*m/s2');
        $this->assertCount(3, $du->unitTerms);
        // Sorted by dimension order: M, L, T.
        $this->assertSame('kg*m/s2', $du->format(true));
    }

    public function testParseNewton(): void
    {
        // Newton is kg⋅m⋅s⁻², dimension MLT-2.
        $du = CompoundUnit::parse('kg*m/s2');
        $this->assertSame('MLT-2', $du->dimension);
    }

    public function testParseWithSuperscriptExponent(): void
    {
        $du = CompoundUnit::parse('m²');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('L2', $du->dimension);
    }

    public function testParseInvalidUnit(): void
    {
        $this->expectException(UnknownUnitException::class);
        CompoundUnit::parse('xyz');
    }

    public function testParseMultipleDivisions(): void
    {
        // m/s/s should be m⋅s⁻²
        $du = CompoundUnit::parse('m/s/s');
        $this->assertSame('m/s2', $du->format(true));
    }

    public function testParseParenthesesMultipleTermsInDenominator(): void
    {
        // J/(mol*K) - energy per amount per temperature
        $du = CompoundUnit::parse('J/(mol*K)');
        $this->assertSame('J/(mol*K)', $du->format(true));
        $this->assertSame('ML2T-2N-1H-1', $du->dimension);
    }

    public function testParseParenthesesSingleTermInDenominator(): void
    {
        // m/(s) - single term in parentheses should work
        $du = CompoundUnit::parse('m/(s)');
        $this->assertSame('m/s', $du->format(true));
    }

    public function testParseParenthesesMultipleTermsInBoth(): void
    {
        // kg*m/(s2*A) - multiple terms in numerator and denominator
        $du = CompoundUnit::parse('kg*m/(s2*A)');
        $this->assertSame('kg*m/(s2*A)', $du->format(true));
    }

    public function testParseParenthesesWithMiddleDot(): void
    {
        // J/(mol·K) - using middle dot separator
        $du = CompoundUnit::parse('J/(mol·K)');
        $this->assertSame('J/(mol*K)', $du->format(true));
    }

    public function testParseParenthesesInNumeratorIsInvalid(): void
    {
        // (kg*m)/s - parentheses in numerator not allowed
        $this->expectException(FormatException::class);
        CompoundUnit::parse('(kg*m)/s');
    }

    public function testParseNestedParenthesesIsInvalid(): void
    {
        // J/((mol*K)) - nested parentheses not allowed
        $this->expectException(FormatException::class);
        CompoundUnit::parse('J/((mol*K))');
    }

    public function testParseUnbalancedParenthesesIsInvalid(): void
    {
        // J/(mol*K - missing closing parenthesis
        $this->expectException(FormatException::class);
        CompoundUnit::parse('J/(mol*K');
    }

    public function testParseEmptyParenthesesIsInvalid(): void
    {
        // m/() - empty parentheses not allowed
        $this->expectException(FormatException::class);
        CompoundUnit::parse('m/()');
    }

    public function testParseFormatRoundTrip(): void
    {
        // Parsing formatted output should produce equivalent unit
        $original = CompoundUnit::parse('J/(mol*K)');
        $formatted = $original->format(true);
        $reparsed = CompoundUnit::parse($formatted);

        $this->assertTrue($original->equal($reparsed));
        $this->assertSame($original->dimension, $reparsed->dimension);
    }

    public function testParseFormatRoundTripUnicode(): void
    {
        // Round-trip with Unicode format
        $original = CompoundUnit::parse('W/(m2*K4)');
        $formatted = $original->format(); // Unicode format
        $reparsed = CompoundUnit::parse($formatted);

        $this->assertTrue($original->equal($reparsed));
    }

    #endregion

    #region Method toCompoundUnit() tests.

    public function testToCompoundUnitFromCompoundUnit(): void
    {
        $original = CompoundUnit::parse('kg*m/s2');
        $result = CompoundUnit::toCompoundUnit($original);

        $this->assertSame($original, $result);
    }

    public function testToCompoundUnitFromString(): void
    {
        $result = CompoundUnit::toCompoundUnit('m/s');

        $this->assertInstanceOf(CompoundUnit::class, $result);
        $this->assertSame('m/s', $result->format(true));
    }

    public function testToCompoundUnitFromUnit(): void
    {
        $meter = UnitService::getBySymbol('m');
        $result = CompoundUnit::toCompoundUnit($meter);

        $this->assertInstanceOf(CompoundUnit::class, $result);
        $this->assertSame('m', $result->format(true));
    }

    public function testToCompoundUnitFromUnitTerm(): void
    {
        $unitTerm = new UnitTerm('m', 'k', 2);
        $result = CompoundUnit::toCompoundUnit($unitTerm);

        $this->assertInstanceOf(CompoundUnit::class, $result);
        $this->assertSame('km2', $result->format(true));
    }

    public function testToCompoundUnitFromNull(): void
    {
        $result = CompoundUnit::toCompoundUnit(null);

        $this->assertInstanceOf(CompoundUnit::class, $result);
        $this->assertSame('', $result->format(true));
    }

    #endregion
}
