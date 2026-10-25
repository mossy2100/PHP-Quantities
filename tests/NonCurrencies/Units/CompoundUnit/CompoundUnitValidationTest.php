<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\CompoundUnit;

use Mossy2100\Quantities\Units\CompoundUnit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the validation methods of CompoundUnit.
 */
#[CoversClass(CompoundUnit::class)]
class CompoundUnitValidationTest extends TestCase
{
    #region Method regex() tests.

    /**
     * Test regex matches a simple unit symbol.
     */
    public function testRegexMatchesSimpleUnit(): void
    {
        $rx = CompoundUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'm'));
    }

    /**
     * Test regex matches a unit with prefix and exponent.
     */
    public function testRegexMatchesUnitWithPrefixAndExponent(): void
    {
        $rx = CompoundUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'km2'));
    }

    /**
     * Test regex matches a compound unit with asterisk separator.
     */
    public function testRegexMatchesCompoundUnitWithAsterisk(): void
    {
        $rx = CompoundUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'kg*m'));
    }

    /**
     * Test regex matches a compound unit with division.
     */
    public function testRegexMatchesCompoundUnitWithDivision(): void
    {
        $rx = CompoundUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'm/s'));
    }

    /**
     * Test regex matches a complex compound unit.
     */
    public function testRegexMatchesComplexCompoundUnit(): void
    {
        $rx = CompoundUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'kg*m/s2'));
    }

    /**
     * Test regex matches a unit with negative exponent.
     */
    public function testRegexMatchesUnitWithNegativeExponent(): void
    {
        $rx = CompoundUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 's-1'));
    }

    /**
     * Test regex matches a unit with superscript exponent.
     */
    public function testRegexMatchesUnitWithSuperscriptExponent(): void
    {
        $rx = CompoundUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'm²'));
    }

    /**
     * Test regex matches parenthesised denominator form.
     */
    public function testRegexMatchesParenthesisedDenominatorForm(): void
    {
        $rx = CompoundUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'J/(mol*K)'));
    }

    /**
     * Test regex matches a unit with middle dot separator.
     */
    public function testRegexMatchesMiddleDotSeparator(): void
    {
        $rx = CompoundUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'kg·m'));
    }

    /**
     * Test regex does not match an empty string.
     */
    public function testRegexDoesNotMatchEmptyString(): void
    {
        $rx = CompoundUnit::regex();

        $this->assertSame(0, preg_match("/^$rx$/iu", ''));
    }

    #endregion
}
