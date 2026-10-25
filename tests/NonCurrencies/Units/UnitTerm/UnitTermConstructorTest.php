<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\UnitTerm;

use DomainException;
use Mossy2100\Quantities\Exceptions\UnknownUnitException;
use Mossy2100\Quantities\Services\PrefixService;
use Mossy2100\Quantities\Services\UnitService;
use Mossy2100\Quantities\Units\Unit;
use Mossy2100\Quantities\Units\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the constructor of UnitTerm.
 */
#[CoversClass(UnitTerm::class)]
final class UnitTermConstructorTest extends TestCase
{
    #region Method __construct() tests.

    /**
     * Test constructor with unit symbol only.
     */
    public function testConstructorWithUnitOnly(): void
    {
        $term = new UnitTerm('m');

        $this->assertSame('meter', $term->unit->name);
        $this->assertNull($term->prefix);
        $this->assertSame(1, $term->exponent);
    }

    /**
     * Test constructor with prefix.
     */
    public function testConstructorWithPrefix(): void
    {
        $term = new UnitTerm('m', 'k');

        $this->assertSame('k', $term->prefix?->asciiSymbol);
        $this->assertSame(1, $term->exponent);
    }

    /**
     * Test constructor with exponent.
     */
    public function testConstructorWithExponent(): void
    {
        $term = new UnitTerm('m', null, 2);

        $this->assertNull($term->prefix);
        $this->assertSame(2, $term->exponent);
    }

    /**
     * Test constructor with prefix and exponent.
     */
    public function testConstructorWithPrefixAndExponent(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $this->assertSame('k', $term->prefix?->asciiSymbol);
        $this->assertSame(2, $term->exponent);
    }

    /**
     * Test constructor with negative exponent.
     */
    public function testConstructorWithNegativeExponent(): void
    {
        $term = new UnitTerm('s', null, -2);

        $this->assertSame(-2, $term->exponent);
    }

    /**
     * Test constructor throws for invalid prefix.
     */
    public function testConstructorThrowsForInvalidPrefix(): void
    {
        $this->expectException(DomainException::class);

        new UnitTerm('m', 'invalid');
    }

    /**
     * Test constructor throws for prefix on unit that doesn't accept prefixes.
     */
    public function testConstructorThrowsForPrefixOnNonPrefixableUnit(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid prefix for unit 'ha': 'k'.");

        new UnitTerm('ha', 'k');
    }

    /**
     * Test constructor throws for exponent of zero.
     */
    public function testConstructorThrowsForExponentZero(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid exponent: 0. Cannot be zero.');

        new UnitTerm('m', null, 0);
    }

    /**
     * Test constructor throws for exponent out of range (positive).
     */
    public function testConstructorThrowsForExponentTooLarge(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Must be in the range [-9, 9].');

        new UnitTerm('m', null, 10);
    }

    /**
     * Test constructor throws for exponent out of range (negative).
     */
    public function testConstructorThrowsForExponentTooSmall(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Must be in the range [-9, 9].');

        new UnitTerm('m', null, -10);
    }

    /**
     * Test constructor with various unit symbols.
     */
    public function testConstructorWithVariousSymbols(): void
    {
        $meter = new UnitTerm('m');
        $this->assertSame('meter', $meter->unit->name);

        $second = new UnitTerm('s');
        $this->assertSame('second', $second->unit->name);

        $hertz = new UnitTerm('Hz');
        $this->assertSame('hertz', $hertz->unit->name);

        $newton = new UnitTerm('N');
        $this->assertSame('newton', $newton->unit->name);
    }

    /**
     * Test constructor throws for unknown unit symbol.
     */
    public function testConstructorThrowsForUnknownSymbol(): void
    {
        $this->expectException(UnknownUnitException::class);
        $this->expectExceptionMessage("Unknown unit: 'xyz'");

        new UnitTerm('xyz');
    }

    /**
     * Test constructor with Unit object instead of string.
     */
    public function testConstructorWithUnitObject(): void
    {
        $unit = UnitService::getBySymbol('m');
        $this->assertInstanceOf(Unit::class, $unit);

        $term = new UnitTerm($unit);
        $this->assertSame($unit, $term->unit);
        $this->assertNull($term->prefix);
        $this->assertSame(1, $term->exponent);
    }

    /**
     * Test constructor with Unit object and prefix string.
     */
    public function testConstructorWithUnitObjectAndPrefixString(): void
    {
        $unit = UnitService::getBySymbol('m');
        $this->assertInstanceOf(Unit::class, $unit);

        $term = new UnitTerm($unit, 'k', 2);

        $this->assertSame($unit, $term->unit);
        $this->assertSame('k', $term->prefix?->asciiSymbol);
        $this->assertSame(2, $term->exponent);
    }

    /**
     * Test constructor with Prefix object instead of string.
     */
    public function testConstructorWithPrefixObject(): void
    {
        $prefix = PrefixService::getBySymbol('k');
        $term = new UnitTerm('m', $prefix, 2);

        $this->assertSame($prefix, $term->prefix);
        $this->assertSame('km2', $term->asciiSymbol);
    }

    /**
     * Test constructor with both Unit and Prefix objects.
     */
    public function testConstructorWithUnitAndPrefixObjects(): void
    {
        $unit = UnitService::getBySymbol('m');
        $this->assertInstanceOf(Unit::class, $unit);

        $prefix = PrefixService::getBySymbol('M');
        $term = new UnitTerm($unit, $prefix, 3);

        $this->assertSame($unit, $term->unit);
        $this->assertSame($prefix, $term->prefix);
        $this->assertSame(3, $term->exponent);
        $this->assertSame('Mm3', $term->asciiSymbol);
    }

    #endregion
}
