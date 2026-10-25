<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\CompoundUnit;

use Mossy2100\Quantities\Units\CompoundUnit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the conversion methods of CompoundUnit.
 */
#[CoversClass(CompoundUnit::class)]
class CompoundUnitConversionTest extends TestCase
{
    #region Method __toString() tests.

    public function testToStringEmpty(): void
    {
        $du = new CompoundUnit();
        $this->assertSame('', (string) $du);
    }

    public function testToStringSingleUnit(): void
    {
        $du = CompoundUnit::parse('m');
        $this->assertSame('m', (string) $du);
    }

    public function testToStringCompoundUnit(): void
    {
        $du = CompoundUnit::parse('m*s-2');
        // __toString returns Unicode format with superscript exponents
        $this->assertSame('m/s²', (string) $du);
    }

    public function testToStringUsesUnicodeSymbol(): void
    {
        // Ohm has Unicode symbol Ω
        $du = CompoundUnit::parse('ohm');
        $this->assertSame('Ω', (string) $du);
    }

    #endregion

    #region Method format() tests.

    public function testFormatAsciiEmpty(): void
    {
        $du = new CompoundUnit();
        $this->assertSame('', $du->format(true));
    }

    public function testFormatAsciiSingleUnit(): void
    {
        $du = CompoundUnit::parse('m');
        $this->assertSame('m', $du->format(true));
    }

    public function testFormatAsciiCompoundUnit(): void
    {
        $du = CompoundUnit::parse('m*s-2');
        // format(true) returns ASCII format with '*' separator
        $this->assertSame('m/s2', $du->format(true));
    }

    public function testFormatAsciiUsesAsciiSymbol(): void
    {
        // Ohm has ASCII symbol 'ohm' and Unicode symbol 'Ω'
        $du = CompoundUnit::parse('ohm');
        $this->assertSame('ohm', $du->format(true));
    }

    public function testFormatUnicodeDefault(): void
    {
        $du = CompoundUnit::parse('m2');
        // format() defaults to Unicode
        $this->assertSame('m²', $du->format());
    }

    public function testFormatUnicodeCompoundUnit(): void
    {
        $du = CompoundUnit::parse('m*s-1');
        $this->assertSame('m/s', $du->format());
    }

    #endregion
}
