<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\UnitTerm;

use Mossy2100\Quantities\Units\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the conversion methods of UnitTerm.
 */
#[CoversClass(UnitTerm::class)]
final class UnitTermConversionTest extends TestCase
{
    #region Method format() tests.

    /**
     * Test format with ASCII mode for base unit.
     */
    public function testFormatAsciiForUnit(): void
    {
        $term = new UnitTerm('m');

        $this->assertSame('m', $term->format(true));
    }

    /**
     * Test format with ASCII mode with prefix and exponent.
     */
    public function testFormatAsciiWithPrefixAndExponent(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $this->assertSame('km2', $term->format(true));
    }

    /**
     * Test format with Unicode mode (default) for base unit.
     */
    public function testFormatUnicodeForUnit(): void
    {
        $term = new UnitTerm('m');

        $this->assertSame('m', $term->format());
    }

    /**
     * Test format with Unicode mode converts exponent to superscript.
     */
    public function testFormatUnicodeConvertsSuperscript(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $this->assertSame('km²', $term->format());
    }

    /**
     * Test format with Unicode mode uses Unicode symbol.
     */
    public function testFormatUnicodeUsesUnicodeSymbol(): void
    {
        $term = new UnitTerm('ohm', 'k');

        $this->assertSame('kΩ', $term->format());
    }

    /**
     * Test format with negative exponent.
     */
    public function testFormatWithNegativeExponent(): void
    {
        $term = new UnitTerm('s', null, -2);

        $this->assertSame('s⁻²', $term->format());
        $this->assertSame('s-2', $term->format(true));
    }

    /**
     * Test format falls back to ASCII symbol when the Unicode symbol contains non-letters.
     *
     * 'deg' has Unicode symbol '°', which is not a letter, so format() uses 'deg' with a
     * superscript exponent rather than producing the unreadable '°²'.
     */
    public function testFormatUsesAsciiSymbolWhenUnicodeContainsNonLetters(): void
    {
        $term = new UnitTerm('deg', null, 2);

        $this->assertSame('deg²', $term->format());
    }

    #endregion

    #region Method __toString() tests.

    /**
     * Test __toString returns Unicode format (with superscript exponent).
     */
    public function testToStringReturnsUnicodeFormat(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $this->assertSame('km²', (string) $term);
    }

    /**
     * Test __toString with negative exponent returns Unicode superscript.
     */
    public function testToStringWithNegativeExponent(): void
    {
        $term = new UnitTerm('s', 'm', -2);

        $this->assertSame('ms⁻²', (string) $term);
    }

    /**
     * Test __toString uses Unicode symbol when available.
     */
    public function testToStringUsesUnicodeSymbol(): void
    {
        $term = new UnitTerm('ohm', 'k');

        $this->assertSame('kΩ', (string) $term);
    }

    /**
     * Test __toString with exponent of 1 omits exponent.
     */
    public function testToStringOmitsExponentOfOne(): void
    {
        $term = new UnitTerm('m');

        $this->assertSame('m', (string) $term);
    }

    #endregion
}
