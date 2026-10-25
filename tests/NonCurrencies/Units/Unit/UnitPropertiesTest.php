<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\Unit;

use Mossy2100\Core\Exceptions\FormatException;
use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\QuantityType;
use Mossy2100\Quantities\Services\PrefixService;
use Mossy2100\Quantities\Services\UnitService;
use Mossy2100\Quantities\Units\Prefix;
use Mossy2100\Quantities\Units\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the properties of Unit.
 */
#[CoversClass(Unit::class)]
final class UnitPropertiesTest extends TestCase
{
    #region Property $asciiSymbol tests.

    /**
     * Test asciiSymbol property can be read.
     */
    public function testAsciiSymbolPropertyIsReadable(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si]
        );

        $this->assertSame('m', $unit->asciiSymbol);
    }

    #endregion

    #region Property $unicodeSymbol tests.

    /**
     * Test unicodeSymbol property can be read.
     */
    public function testUnicodeSymbolPropertyIsReadable(): void
    {
        $unit = new Unit(
            name: 'ohm',
            asciiSymbol: 'ohm',
            dimension: 'T-3L2MI-2',
            systems: [UnitSystem::Si],
            unicodeSymbol: 'Ω'
        );

        $this->assertSame('Ω', $unit->unicodeSymbol);
    }

    /**
     * Test unicodeSymbol defaults to asciiSymbol when not specified.
     */
    public function testUnicodeSymbolDefaultsToAsciiSymbol(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si]
        );

        $this->assertSame('m', $unit->unicodeSymbol);
    }

    #endregion

    #region Property $dimension tests.

    /**
     * Test dimension property can be read.
     */
    public function testDimensionPropertyIsReadable(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si]
        );

        $this->assertSame('L', $unit->dimension);
    }

    #endregion

    #region Property $allowedPrefixes tests.

    /**
     * Test allowedPrefixes property returns array of Prefix objects.
     */
    public function testAllowedPrefixesPropertyReturnsArray(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si],
            prefixGroup: PrefixService::GROUP_METRIC
        );

        $prefixes = $unit->allowedPrefixes;

        $this->assertIsArray($prefixes);
        $this->assertNotEmpty($prefixes);
    }

    /**
     * Test allowedPrefixes property returns empty array when no prefixes allowed.
     */
    public function testAllowedPrefixesPropertyReturnsEmptyArray(): void
    {
        $unit = new Unit(
            name: 'hectare',
            asciiSymbol: 'ha',
            dimension: 'L2',
            systems: [UnitSystem::SiAccepted]
        );

        $prefixes = $unit->allowedPrefixes;

        $this->assertSame([], $prefixes);
    }

    #endregion

    #region Property $quantityType tests.

    /**
     * Test quantityType property returns QuantityType for a registered dimension.
     */
    public function testQuantityTypePropertyReturnsQuantityType(): void
    {
        $unit = UnitService::getBySymbol('m');
        $this->assertInstanceOf(Unit::class, $unit);

        $quantityType = $unit->quantityType;
        $this->assertInstanceOf(QuantityType::class, $quantityType);
        $this->assertSame('length', $quantityType->name);
    }

    /**
     * Test quantityType property returns null for a unit with unregistered dimension.
     */
    public function testQuantityTypePropertyReturnsNullForUnregisteredDimension(): void
    {
        $unit = new Unit(
            name: 'unregistered test',
            asciiSymbol: 'Xur',
            dimension: 'L5T3',
            systems: [UnitSystem::Custom]
        );

        $this->assertNull($unit->quantityType);
    }

    #endregion

    #region UnitService integration tests.

    /**
     * Test getting meter from UnitService.
     */
    public function testGetMeterFromService(): void
    {
        $unit = UnitService::getBySymbol('m');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertSame('meter', $unit->name);
        $this->assertSame('m', $unit->asciiSymbol);
        $this->assertSame('L', $unit->dimension);
    }

    /**
     * Test getting ohm from UnitService.
     */
    public function testGetOhmFromService(): void
    {
        $unit = UnitService::getBySymbol('ohm');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertSame('ohm', $unit->asciiSymbol);
        $this->assertSame('Ω', $unit->unicodeSymbol);
        $this->assertSame('Ω', $unit->format());
        $this->assertSame('Ω', (string) $unit);
        $this->assertSame('ohm', $unit->format(true));
    }

    /**
     * Test getting byte from UnitService.
     */
    public function testGetByteFromService(): void
    {
        $unit = UnitService::getBySymbol('B');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertSame('byte', $unit->name);
        $this->assertSame('B', $unit->asciiSymbol);

        // Should accept both binary and large metric prefixes.
        $this->assertTrue($unit->acceptsPrefix(self::prefix('Ki')));
        $this->assertTrue($unit->acceptsPrefix(self::prefix('Mi')));
        $this->assertTrue($unit->acceptsPrefix(self::prefix('k')));
        $this->assertTrue($unit->acceptsPrefix(self::prefix('M')));

        // Should not accept small metric prefixes.
        $this->assertFalse($unit->acceptsPrefix(self::prefix('m')));
        $this->assertFalse($unit->acceptsPrefix(self::prefix('c')));
    }

    #endregion

    #region Property $alternateSymbol tests.

    /**
     * Test alternateSymbol property is set correctly.
     */
    public function testAlternateSymbolPropertyIsSet(): void
    {
        $unit = new Unit(
            name: 'liter',
            asciiSymbol: 'L',
            dimension: 'L3',
            systems: [UnitSystem::SiAccepted],
            alternateSymbol: '#'
        );

        $this->assertSame('#', $unit->alternateSymbol);
    }

    /**
     * Test constructor throws for invalid alternate symbol.
     */
    public function testConstructorThrowsForInvalidAlternateSymbol(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid alternate unit symbol');

        new Unit(
            name: 'liter',
            asciiSymbol: 'L',
            dimension: 'L3',
            systems: [UnitSystem::SiAccepted],
            alternateSymbol: '*'
        );
    }

    /**
     * Test alternateSymbol accepts a single letter.
     */
    public function testAlternateSymbolAcceptsSingleLetter(): void
    {
        $unit = new Unit(
            name: 'liter',
            asciiSymbol: 'L',
            dimension: 'L3',
            systems: [UnitSystem::SiAccepted],
            alternateSymbol: 'l'
        );

        $this->assertSame('l', $unit->alternateSymbol);
    }

    /**
     * Test alternateSymbol accepts a word of several letters.
     */
    public function testAlternateSymbolAcceptsWord(): void
    {
        $unit = new Unit(name: 'test', asciiSymbol: 'tst', dimension: 'L', alternateSymbol: 'μtst');

        $this->assertSame('μtst', $unit->alternateSymbol);
    }

    /**
     * Test alternateSymbol property is null when not set.
     */
    public function testAlternateSymbolPropertyIsNullByDefault(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si]
        );

        $this->assertNull($unit->alternateSymbol);
    }

    #endregion

    #region Property $symbols tests.

    /**
     * Test symbols property returns array with ASCII symbol.
     */
    public function testSymbolsPropertyIncludesAsciiSymbol(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si]
        );

        $symbols = $unit->symbols;

        $this->assertIsArray($symbols);
        $this->assertArrayHasKey('m', $symbols);
    }

    /**
     * Test symbols property includes Unicode symbol when different.
     */
    public function testSymbolsPropertyIncludesUnicodeSymbol(): void
    {
        $unit = new Unit(
            name: 'ohm',
            asciiSymbol: 'ohm',
            dimension: 'T-3L2MI-2',
            systems: [UnitSystem::Si],
            unicodeSymbol: 'Ω'
        );

        $symbols = $unit->symbols;

        $this->assertIsArray($symbols);
        $this->assertArrayHasKey('ohm', $symbols);
        $this->assertArrayHasKey('Ω', $symbols);
    }

    /**
     * Test symbols property includes alternate symbol when set.
     */
    public function testSymbolsPropertyIncludesAlternateSymbol(): void
    {
        $unit = new Unit(
            name: 'liter',
            asciiSymbol: 'L',
            dimension: 'L3',
            systems: [UnitSystem::SiAccepted],
            alternateSymbol: '#'
        );

        $symbols = $unit->symbols;

        $this->assertIsArray($symbols);
        $this->assertArrayHasKey('L', $symbols);
        $this->assertArrayHasKey('#', $symbols);
    }

    /**
     * Test symbols property includes prefixed symbols.
     */
    public function testSymbolsPropertyIncludesPrefixedSymbols(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si],
            prefixGroup: PrefixService::GROUP_METRIC
        );

        $symbols = $unit->symbols;

        $this->assertIsArray($symbols);
        $this->assertArrayHasKey('m', $symbols);
        $this->assertArrayHasKey('km', $symbols);
        $this->assertArrayHasKey('cm', $symbols);
        $this->assertArrayHasKey('mm', $symbols);
    }

    /**
     * Test symbols property includes prefixed Unicode symbols.
     */
    public function testSymbolsPropertyIncludesPrefixedUnicodeSymbols(): void
    {
        $unit = new Unit(
            name: 'ohm',
            asciiSymbol: 'ohm',
            dimension: 'T-3L2MI-2',
            systems: [UnitSystem::Si],
            prefixGroup: PrefixService::GROUP_METRIC,
            unicodeSymbol: 'Ω'
        );

        $symbols = $unit->symbols;

        // ASCII prefixed.
        $this->assertIsArray($symbols);
        $this->assertArrayHasKey('kohm', $symbols);
        $this->assertArrayHasKey('Mohm', $symbols);

        // Unicode symbol prefixed.
        $this->assertArrayHasKey('kΩ', $symbols);
        $this->assertArrayHasKey('MΩ', $symbols);
    }

    #endregion

    #region Helper methods

    /**
     * Resolve a prefix symbol to a Prefix instance. Asserts the lookup succeeded, so the return type narrows
     * from ?Prefix to Prefix for the caller.
     */
    private static function prefix(string $symbol): Prefix
    {
        $prefix = PrefixService::getBySymbol($symbol);
        self::assertNotNull($prefix, "Prefix '$symbol' not found");
        return $prefix;
    }

    #endregion
}
