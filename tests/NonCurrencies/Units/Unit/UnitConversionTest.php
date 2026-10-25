<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\Unit;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Units\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the conversion methods of Unit.
 */
#[CoversClass(Unit::class)]
final class UnitConversionTest extends TestCase
{
    #region Method __toString() tests.

    /**
     * Test __toString returns the Unicode symbol.
     */
    public function testToStringReturnsUnicodeSymbol(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si]
        );

        $this->assertSame('m', (string) $unit);
    }

    /**
     * Test __toString returns Unicode symbol when different from ASCII symbol.
     */
    public function testToStringReturnsUnicodeSymbolWhenDifferent(): void
    {
        $unit = new Unit(
            name: 'ohm',
            asciiSymbol: 'ohm',
            dimension: 'T-3L2MI-2',
            systems: [UnitSystem::Si],
            unicodeSymbol: 'Ω'
        );

        $this->assertSame('Ω', (string) $unit);
    }

    #endregion

    #region Method format() tests.

    /**
     * Test format() returns the Unicode symbol by default.
     */
    public function testFormatReturnsUnicodeSymbol(): void
    {
        $unit = new Unit(
            name: 'ohm',
            asciiSymbol: 'ohm',
            dimension: 'T-3L2MI-2',
            systems: [UnitSystem::Si],
            unicodeSymbol: 'Ω'
        );

        $this->assertSame('Ω', $unit->format());
        $this->assertSame('Ω', $unit->format(false));
    }

    /**
     * Test format(true) returns the ASCII symbol.
     */
    public function testFormatAsciiReturnsSymbol(): void
    {
        $unit = new Unit(
            name: 'ohm',
            asciiSymbol: 'ohm',
            dimension: 'T-3L2MI-2',
            systems: [UnitSystem::Si],
            unicodeSymbol: 'Ω'
        );

        $this->assertSame('ohm', $unit->format(true));
    }

    /**
     * Test format() with degree symbol.
     */
    public function testFormatWithDegreeSymbol(): void
    {
        $unit = new Unit(
            name: 'degree',
            asciiSymbol: 'deg',
            dimension: 'A',
            systems: [UnitSystem::SiAccepted],
            unicodeSymbol: '°'
        );

        $this->assertSame('°', (string) $unit);
        $this->assertSame('°', $unit->format());
        $this->assertSame('deg', $unit->format(true));
    }

    #endregion
}
