<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\Unit;

use Mossy2100\Core\Exceptions\FormatException;
use Mossy2100\Quantities\Exceptions\UnknownUnitException;
use Mossy2100\Quantities\Units\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the factory methods of Unit.
 */
#[CoversClass(Unit::class)]
final class UnitFactoryTest extends TestCase
{
    #region Method parse() tests.

    /**
     * Test parse returns Unit for valid symbol.
     */
    public function testParseReturnsUnitForValidSymbol(): void
    {
        $unit = Unit::parse('m');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertSame('meter', $unit->name);
        $this->assertSame('m', $unit->asciiSymbol);
    }

    /**
     * Test parse returns Unit for Unicode symbol.
     */
    public function testParseReturnsUnitForUnicodeSymbol(): void
    {
        $unit = Unit::parse('Ω');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertSame('ohm', $unit->name);
    }

    /**
     * Test parse throws FormatException for invalid characters.
     */
    public function testParseThrowsFormatExceptionForInvalidCharacters(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid unit symbol format');

        Unit::parse('m²');
    }

    /**
     * Test parse throws UnknownUnitException for unknown symbol.
     */
    public function testParseThrowsUnknownUnitExceptionForUnknownSymbol(): void
    {
        $this->expectException(UnknownUnitException::class);
        $this->expectExceptionMessage("Unknown unit: 'xyz'");

        Unit::parse('xyz');
    }

    /**
     * Test parse returns the same cached instance on repeated calls.
     */
    public function testParseCachesResult(): void
    {
        $this->assertSame(Unit::parse('m'), Unit::parse('m'));
    }

    /**
     * Test parse does not cache failures — a previously-failed lookup still throws on subsequent calls.
     */
    public function testParseDoesNotCacheFailures(): void
    {
        try {
            Unit::parse('zqx');
        } catch (UnknownUnitException) {
            // Expected.
        }

        $this->expectException(UnknownUnitException::class);
        Unit::parse('zqx');
    }

    #endregion
}
