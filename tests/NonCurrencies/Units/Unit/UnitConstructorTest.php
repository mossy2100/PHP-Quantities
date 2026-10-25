<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\Unit;

use DomainException;
use InvalidArgumentException;
use Mossy2100\Core\Exceptions\FormatException;
use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Services\PrefixService;
use Mossy2100\Quantities\Units\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the constructor of Unit.
 */
#[CoversClass(Unit::class)]
final class UnitConstructorTest extends TestCase
{
    #region Method __construct() tests.

    /**
     * Test constructor with SI base unit data (meter).
     */
    public function testConstructorWithSiBaseUnit(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si],
            prefixGroup: PrefixService::GROUP_METRIC
        );

        $this->assertSame('meter', $unit->name);
        $this->assertSame('m', $unit->asciiSymbol);
        $this->assertSame('m', $unit->unicodeSymbol);
        $this->assertSame('L', $unit->dimension);
        $this->assertContains(UnitSystem::Si, $unit->systems);
        $this->assertSame(PrefixService::GROUP_METRIC, $unit->prefixGroup);
    }

    /**
     * Test constructor with SI named unit data (hertz).
     */
    public function testConstructorWithSiNamedUnit(): void
    {
        $unit = new Unit(
            name: 'hertz',
            asciiSymbol: 'Hz',
            dimension: 'T-1',
            systems: [UnitSystem::Si],
            prefixGroup: PrefixService::GROUP_METRIC
        );

        $this->assertSame('hertz', $unit->name);
        $this->assertSame('Hz', $unit->asciiSymbol);
        $this->assertSame('Hz', $unit->unicodeSymbol);
        $this->assertSame('T-1', $unit->dimension);
    }

    /**
     * Test constructor with custom Unicode symbol (ohm).
     */
    public function testConstructorWithCustomUnicodeSymbol(): void
    {
        $unit = new Unit(
            name: 'ohm',
            asciiSymbol: 'ohm',
            dimension: 'T-3L2MI-2',
            systems: [UnitSystem::Si],
            prefixGroup: PrefixService::GROUP_METRIC,
            unicodeSymbol: 'Ω'
        );

        $this->assertSame('ohm', $unit->asciiSymbol);
        $this->assertSame('Ω', $unit->unicodeSymbol);
    }

    /**
     * Test constructor with no prefix group (defaults to 0).
     */
    public function testConstructorWithNoPrefixGroup(): void
    {
        $unit = new Unit(
            name: 'hectare',
            asciiSymbol: 'ha',
            dimension: 'L2',
            systems: [UnitSystem::SiAccepted]
        );

        $this->assertSame(0, $unit->prefixGroup);
    }

    /**
     * Test constructor normalizes dimension code.
     */
    public function testConstructorNormalizesDimension(): void
    {
        $unit = new Unit(
            name: 'newton',
            asciiSymbol: 'N',
            dimension: 'MLT-2',
            systems: [UnitSystem::Si]
        );

        // Dimension should be normalized.
        $this->assertSame('MLT-2', $unit->dimension);
    }

    /**
     * Test constructor throws for invalid ASCII symbol.
     */
    public function testConstructorThrowsForInvalidAsciiSymbol(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid ASCII unit symbol');

        new Unit(
            name: 'test',
            asciiSymbol: 'm²',
            dimension: 'L',
            systems: [UnitSystem::Si]
        );
    }

    /**
     * Test constructor throws for invalid Unicode symbol.
     */
    public function testConstructorThrowsForInvalidUnicodeSymbol(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid Unicode unit symbol');

        new Unit(
            name: 'test',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si],
            unicodeSymbol: '123'
        );
    }

    /**
     * Test constructor accepts a Unicode symbol that's a word of several letters.
     */
    public function testConstructorAcceptsUnicodeWordSymbol(): void
    {
        $unit = new Unit(name: 'test', asciiSymbol: 'tst', dimension: 'L', unicodeSymbol: 'µtst');

        $this->assertSame('µtst', $unit->unicodeSymbol);
    }

    /**
     * Test constructor throws for a Unicode word symbol longer than 7 letters.
     */
    public function testConstructorThrowsForTooLongUnicodeWordSymbol(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid Unicode unit symbol');

        new Unit(name: 'test', asciiSymbol: 'tst', dimension: 'L', unicodeSymbol: 'µabcdefg');
    }

    /**
     * Test constructor defaults systems to [UnitSystem::Custom] when not specified.
     */
    public function testConstructorDefaultsSystemToCustom(): void
    {
        $unit = new Unit(name: 'test', asciiSymbol: 'tst', dimension: 'L');

        $this->assertCount(1, $unit->systems);
        $this->assertContains(UnitSystem::Custom, $unit->systems);
    }

    /**
     * Test constructor throws for empty name.
     */
    public function testConstructorThrowsForEmptyName(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid unit name');

        new Unit(name: '', asciiSymbol: 'tst', dimension: 'L');
    }

    /**
     * Test constructor throws for name containing digits.
     */
    public function testConstructorThrowsForNameWithDigits(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid unit name');

        new Unit(name: 'unit123', asciiSymbol: 'tst', dimension: 'L');
    }

    /**
     * Test constructor accepts name with up to three words.
     */
    public function testConstructorAcceptsThreeWordName(): void
    {
        $unit = new Unit(name: 'US fluid ounce', asciiSymbol: 'floz', dimension: 'L3');

        $this->assertSame('US fluid ounce', $unit->name);
    }

    /**
     * Test constructor accepts names with Latin diacritics (e.g. currency name Bolívar Soberano).
     */
    public function testConstructorAcceptsLatinDiacritics(): void
    {
        $unit = new Unit(name: 'Bolívar Soberano', asciiSymbol: 'VES', dimension: 'C');

        $this->assertSame('Bolívar Soberano', $unit->name);
    }

    /**
     * Test constructor accepts the right single quotation mark (U+2019), used by Pa’anga (Tonga).
     */
    public function testConstructorAcceptsRightSingleQuote(): void
    {
        $unit = new Unit(name: "Pa\u{2019}anga", asciiSymbol: 'TOP', dimension: 'C');

        $this->assertSame("Pa\u{2019}anga", $unit->name);
    }

    /**
     * Test constructor rejects names with non-Latin letters (e.g. Greek).
     */
    public function testConstructorThrowsForNonLatinScriptName(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid unit name');

        new Unit(name: "\u{03BC}icro", asciiSymbol: 'u', dimension: 'L');
    }

    /**
     * Test constructor rejects names containing punctuation other than the allowed right single quote.
     */
    public function testConstructorThrowsForNameWithPunctuation(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid unit name');

        new Unit(name: 'meter-squared', asciiSymbol: 'm2', dimension: 'L2');
    }

    /**
     * Test constructor throws for empty systems array.
     */
    public function testConstructorThrowsForEmptySystems(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot create Unit with no measurement systems.');

        new Unit(
            name: 'test',
            asciiSymbol: 'tst',
            dimension: 'L',
            systems: []
        );
    }

    /**
     * Test constructor throws for non-UnitSystem values in systems array.
     */
    public function testConstructorThrowsForNonSystemValues(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid system type: string. Must be UnitSystem.');

        new Unit(
            name: 'test',
            asciiSymbol: 'tst',
            dimension: 'L',
            systems: ['SI', 'Imperial'] // @phpstan-ignore argument.type
        );
    }

    /**
     * Test constructor deduplicates systems.
     */
    public function testConstructorDeduplicatesSystems(): void
    {
        $unit = new Unit(
            name: 'test',
            asciiSymbol: 'tst',
            dimension: 'L',
            systems: [UnitSystem::Si, UnitSystem::Si, UnitSystem::Imperial]
        );

        $this->assertCount(2, $unit->systems);
    }

    /**
     * Test constructor throws for prefix group below range.
     */
    public function testConstructorThrowsForNegativePrefixGroup(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid prefix group');

        new Unit(name: 'test', asciiSymbol: 'tst', dimension: 'L', prefixGroup: -1);
    }

    /**
     * Test constructor throws for prefix group above range.
     */
    public function testConstructorThrowsForPrefixGroupAboveRange(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid prefix group');

        new Unit(name: 'test', asciiSymbol: 'tst', dimension: 'L', prefixGroup: 16);
    }

    #endregion
}
