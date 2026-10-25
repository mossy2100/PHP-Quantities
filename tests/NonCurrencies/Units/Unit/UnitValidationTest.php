<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\Unit;

use Mossy2100\Quantities\Units\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the validation methods of Unit.
 */
#[CoversClass(Unit::class)]
final class UnitValidationTest extends TestCase
{
    #region Method isValidLetter() tests.

    /**
     * Test isValidLetter() accepts Latin, Greek, and letter-like compatibility characters; rejects non-letters
     * and multi-char inputs.
     */
    public function testIsValidLetter(): void
    {
        // Single letters → true.
        $this->assertTrue(Unit::isValidLetter('a'));
        $this->assertTrue(Unit::isValidLetter('A'));
        $this->assertTrue(Unit::isValidLetter("\u{03BC}"));   // Greek mu
        $this->assertTrue(Unit::isValidLetter("\u{00B5}"));   // Micro sign
        $this->assertTrue(Unit::isValidLetter("\u{03A9}"));   // Greek capital omega
        $this->assertTrue(Unit::isValidLetter("\u{2126}"));   // Ohm sign (letterlike compatibility char)

        // Non-letters → false.
        $this->assertFalse(Unit::isValidLetter('°'));
        $this->assertFalse(Unit::isValidLetter('%'));
        $this->assertFalse(Unit::isValidLetter('1'));

        // Multi-character strings → false.
        $this->assertFalse(Unit::isValidLetter('km'));
        $this->assertFalse(Unit::isValidLetter('°C'));

        // Empty string → false.
        $this->assertFalse(Unit::isValidLetter(''));
    }

    #endregion

    #region Method isValidNonLetter() tests.

    /**
     * Test isValidNonLetter() returns true for single valid ASCII or Unicode non-letters, and false for letters,
     * multi-character strings, and the empty string.
     */
    public function testIsValidNonLetter(): void
    {
        // Single valid non-letters → true.
        $this->assertTrue(Unit::isValidNonLetter('°'));
        $this->assertTrue(Unit::isValidNonLetter('%'));
        $this->assertTrue(Unit::isValidNonLetter('″'));

        // Letters → false.
        $this->assertFalse(Unit::isValidNonLetter('m'));
        $this->assertFalse(Unit::isValidNonLetter('A'));

        // Multi-character strings → false (even if they start with a non-letter).
        $this->assertFalse(Unit::isValidNonLetter('°C'));
        $this->assertFalse(Unit::isValidNonLetter('km'));

        // Empty string → false.
        $this->assertFalse(Unit::isValidNonLetter(''));
    }

    #endregion

    #region Method isValidWord() tests.

    /**
     * Test isValidWord() accepts 1–7 Unicode letters and rejects non-letters, multi-char symbols, and empty strings.
     */
    public function testIsValidWord(): void
    {
        // Single ASCII letter.
        $this->assertTrue(Unit::isValidWord('m'));
        // Multi-letter ASCII word.
        $this->assertTrue(Unit::isValidWord('km'));
        $this->assertTrue(Unit::isValidWord('arcmin'));
        // Exactly 7 letters — boundary.
        $this->assertTrue(Unit::isValidWord('abcdefg'));
        // Single Unicode letter.
        $this->assertTrue(Unit::isValidWord("\u{03BC}"));  // Greek mu

        // 8 letters — exceeds maximum.
        $this->assertFalse(Unit::isValidWord('abcdefgh'));
        // Non-letter character.
        $this->assertFalse(Unit::isValidWord('°'));
        $this->assertFalse(Unit::isValidWord('%'));
        // Contains a non-letter.
        $this->assertFalse(Unit::isValidWord('km/h'));
        $this->assertFalse(Unit::isValidWord('k m'));
        // Digit.
        $this->assertFalse(Unit::isValidWord('1m'));
        // Empty string → false.
        $this->assertFalse(Unit::isValidWord(''));
    }

    #endregion
}
