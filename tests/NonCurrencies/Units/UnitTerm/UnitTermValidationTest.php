<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\UnitTerm;

use Mossy2100\Quantities\Units\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests for the validation methods of UnitTerm.
 */
#[CoversClass(UnitTerm::class)]
final class UnitTermValidationTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset the cached regex so UnitTerm::regex() always rebuilds during this test class,
        // ensuring coverage is attributed here regardless of test execution order.
        $prop = new ReflectionClass(UnitTerm::class)->getProperty('rx');
        $prop->setValue(null, null);
    }

    #region Method regex() tests.

    /**
     * Test regex matches simple unit.
     */
    public function testRegexMatchesSimpleUnit(): void
    {
        $pattern = '/^' . UnitTerm::regex() . '$/iu';

        $this->assertSame(1, preg_match($pattern, 'm'));
        $this->assertSame(1, preg_match($pattern, 'km'));
        $this->assertSame(1, preg_match($pattern, 'Hz'));
    }

    /**
     * Test regex matches unit with ASCII exponent.
     */
    public function testRegexMatchesUnitWithAsciiExponent(): void
    {
        $pattern = '/^' . UnitTerm::regex() . '$/iu';

        $this->assertSame(1, preg_match($pattern, 'm2'));
        $this->assertSame(1, preg_match($pattern, 's-2'));
        $this->assertSame(1, preg_match($pattern, 'km3'));
    }

    /**
     * Test regex matches unit with superscript exponent.
     */
    public function testRegexMatchesUnitWithSuperscriptExponent(): void
    {
        $pattern = '/^' . UnitTerm::regex() . '$/iu';

        $this->assertSame(1, preg_match($pattern, 'm²'));
        $this->assertSame(1, preg_match($pattern, 's⁻²'));
        $this->assertSame(1, preg_match($pattern, 'km³'));
    }

    /**
     * Test regex does not match invalid formats.
     */
    public function testRegexDoesNotMatchInvalidFormats(): void
    {
        $pattern = '/^' . UnitTerm::regex() . '$/iu';

        $this->assertSame(0, preg_match($pattern, '123'));
    }

    #endregion

    #region Method isValidSymbol() tests.

    /**
     * Test isValidSymbol() returns true and populates match groups for valid unit term strings.
     */
    public function testIsValidSymbolReturnsTrueForValidFormats(): void
    {
        // Single letter — group 1 is the symbol, group 2 absent.
        $this->assertTrue(UnitTerm::isValidSymbol('m', $m));
        $this->assertIsArray($m);
        $this->assertArrayHasKey(1, $m);
        $this->assertSame('m', $m[1]);
        $this->assertArrayNotHasKey(2, $m);

        // Multi-letter word (prefixed symbol passes format check).
        $this->assertTrue(UnitTerm::isValidSymbol('km', $m));
        $this->assertIsArray($m);
        $this->assertArrayHasKey(1, $m);
        $this->assertSame('km', $m[1]);

        // Letter with ASCII exponent — group 2 is the exponent.
        $this->assertTrue(UnitTerm::isValidSymbol('m2', $m));
        $this->assertIsArray($m);
        $this->assertArrayHasKey(1, $m);
        $this->assertSame('m', $m[1]);
        $this->assertSame('2', $m[2]);

        // Negative ASCII exponent.
        $this->assertTrue(UnitTerm::isValidSymbol('m-2', $m));
        $this->assertIsArray($m);
        $this->assertArrayHasKey(1, $m);
        $this->assertSame('m', $m[1]);
        $this->assertArrayHasKey(2, $m);
        $this->assertSame('-2', $m[2]);

        // Non-letter symbol.
        $this->assertTrue(UnitTerm::isValidSymbol('%', $m));
        $this->assertIsArray($m);
        $this->assertArrayHasKey(1, $m);
        $this->assertSame('%', $m[1]);

        // Temperature symbol (degree + uppercase letter).
        $this->assertTrue(UnitTerm::isValidSymbol('°C', $m));
        $this->assertIsArray($m);
        $this->assertArrayHasKey(1, $m);
        $this->assertSame('°C', $m[1]);

        // Unicode superscript exponent.
        $this->assertTrue(UnitTerm::isValidSymbol("m\u{00B2}", $m));  // m²
        $this->assertIsArray($m);
        $this->assertArrayHasKey(1, $m);
        $this->assertSame('m', $m[1]);
        $this->assertArrayHasKey(2, $m);
        $this->assertSame('²', $m[2]);
    }

    /**
     * Test isValidSymbol() returns false for strings that don't match the unit term format.
     */
    public function testIsValidSymbolReturnsFalseForInvalidFormats(): void
    {
        // Empty string.
        $this->assertFalse(UnitTerm::isValidSymbol('', $m));

        // Compound unit — slash not valid in a single term.
        $this->assertFalse(UnitTerm::isValidSymbol('m/s', $m));

        // Multiply operator.
        $this->assertFalse(UnitTerm::isValidSymbol('m*s', $m));

        // Starts with a digit.
        $this->assertFalse(UnitTerm::isValidSymbol('2m', $m));
    }

    #endregion
}
