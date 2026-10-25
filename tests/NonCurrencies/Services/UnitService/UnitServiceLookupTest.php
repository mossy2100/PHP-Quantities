<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Services\UnitService;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Services\QuantityTypeService;
use Mossy2100\Quantities\Services\UnitService;
use Mossy2100\Quantities\Units\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the lookup methods of UnitService.
 */
#[CoversClass(UnitService::class)]
final class UnitServiceLookupTest extends TestCase
{
    #region Method getAll() tests.

    /**
     * Test getAll() returns an array.
     */
    public function testGetAllReturnsArray(): void
    {
        $result = UnitService::getAll();

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertIsArray($result);
    }

    /**
     * Test getAll() returns Unit objects.
     */
    public function testGetAllReturnsUnitObjects(): void
    {
        $result = UnitService::getAll();

        foreach ($result as $name => $unit) {
            $this->assertIsString($name);
            $this->assertInstanceOf(Unit::class, $unit);
        }
    }

    /**
     * Test getAll() contains SI base units.
     */
    public function testGetAllContainsSiBaseUnits(): void
    {
        $result = UnitService::getAll();

        $this->assertArrayHasKey('meter', $result);
        $this->assertArrayHasKey('gram', $result);  // SI uses kilogram, but gram is the base unit
        $this->assertArrayHasKey('second', $result);
        $this->assertArrayHasKey('ampere', $result);
        $this->assertArrayHasKey('kelvin', $result);
        $this->assertArrayHasKey('mole', $result);
        $this->assertArrayHasKey('candela', $result);
    }

    /**
     * Test getAll() units have correct symbols.
     */
    public function testGetAllUnitsHaveCorrectSymbols(): void
    {
        $result = UnitService::getAll();

        $this->assertSame('m', $result['meter']->asciiSymbol);
        $this->assertSame('g', $result['gram']->asciiSymbol);
        $this->assertSame('s', $result['second']->asciiSymbol);
    }

    #endregion

    #region Method getByName() tests.

    /**
     * Test getByName() returns Unit for a valid name.
     */
    public function testGetByNameReturnsUnitForValidName(): void
    {
        $result = UnitService::getByName('meter');

        $this->assertInstanceOf(Unit::class, $result);
        $this->assertSame('meter', $result->name);
        $this->assertSame('m', $result->asciiSymbol);
    }

    /**
     * Test getByName() returns null for unknown name.
     */
    public function testGetByNameReturnsNullForUnknown(): void
    {
        $this->assertNull(UnitService::getByName('nonexistent_xyz'));
    }

    /**
     * Test getByName() is case sensitive.
     */
    public function testGetByNameIsCaseSensitive(): void
    {
        $this->assertInstanceOf(Unit::class, UnitService::getByName('meter'));
        $this->assertNull(UnitService::getByName('Meter'));
        $this->assertNull(UnitService::getByName('METER'));
    }

    #endregion

    #region Method getBySymbol() tests.

    /**
     * Test getBySymbol() returns Unit for valid ASCII symbol.
     */
    public function testGetBySymbolReturnsUnitForAsciiSymbol(): void
    {
        $result = UnitService::getBySymbol('m');

        $this->assertInstanceOf(Unit::class, $result);
        $this->assertSame('meter', $result->name);
        $this->assertSame('m', $result->asciiSymbol);
    }

    /**
     * Test getBySymbol() returns Unit for Unicode symbol.
     */
    public function testGetBySymbolReturnsUnitForUnicodeSymbol(): void
    {
        // Ohm has Unicode symbol Ω.
        $result = UnitService::getBySymbol('Ω');

        $this->assertInstanceOf(Unit::class, $result);
        $this->assertSame('ohm', $result->name);
    }

    /**
     * Test getBySymbol() returns Unit for prefixed symbol.
     */
    public function testGetBySymbolReturnsUnitForPrefixedSymbol(): void
    {
        $result = UnitService::getBySymbol('km');

        $this->assertInstanceOf(Unit::class, $result);
        $this->assertSame('meter', $result->name);
    }

    /**
     * Test getBySymbol() finds every registered unit by each of its symbols.
     */
    public function testGetBySymbolFindsEveryUnitBySymbol(): void
    {
        $nSymbols = 0;
        foreach (UnitService::getAll() as $unit) {
            $symbols = $unit->symbols;
            $this->assertIsArray($symbols);
            foreach (array_keys($symbols) as $symbol) {
                $this->assertSame($unit, UnitService::getBySymbol($symbol), "Symbol '$symbol'.");
            }
            $nSymbols += count($symbols);
        }

        $this->assertCount($nSymbols, UnitService::getAllSymbols());
    }

    /**
     * Test getBySymbol() returns null for unknown symbol.
     */
    public function testGetBySymbolReturnsNullForUnknown(): void
    {
        $this->assertNull(UnitService::getBySymbol('xyz'));
    }

    /**
     * Test getBySymbol() is case sensitive.
     */
    public function testGetBySymbolIsCaseSensitive(): void
    {
        // 'm' is meter, 'M' alone is not a unit.
        $meter = UnitService::getBySymbol('m');
        $this->assertInstanceOf(Unit::class, $meter);
        $this->assertSame('meter', $meter->name);

        $upper = UnitService::getBySymbol('M');
        $this->assertNull($upper);
    }

    #endregion

    #region Method getBySystem() tests.

    /**
     * Test getBySystem() returns an array of Unit objects.
     */
    public function testGetBySystemReturnsUnitObjects(): void
    {
        $result = UnitService::getBySystem(UnitSystem::Si);

        $this->assertNotEmpty($result);
        foreach ($result as $unit) {
            $this->assertInstanceOf(Unit::class, $unit);
        }
    }

    /**
     * Test getBySystem() returns only units belonging to the specified system.
     */
    public function testGetBySystemReturnsOnlyMatchingUnits(): void
    {
        $result = UnitService::getBySystem(UnitSystem::Si);

        foreach ($result as $unit) {
            $this->assertTrue(
                $unit->belongsToSystem(UnitSystem::Si),
                "Unit '$unit->name' does not belong to SI system."
            );
        }
    }

    /**
     * Test getBySystem() includes SI base units for SI system.
     */
    public function testGetBySystemIncludesSiBaseUnits(): void
    {
        $result = UnitService::getBySystem(UnitSystem::Si);
        $symbols = array_map(static fn (Unit $u) => $u->asciiSymbol, $result);

        $this->assertContains('m', $symbols);
        $this->assertContains('g', $symbols);
        $this->assertContains('s', $symbols);
        $this->assertContains('A', $symbols);
        $this->assertContains('K', $symbols);
        $this->assertContains('mol', $symbols);
        $this->assertContains('cd', $symbols);
    }

    /**
     * Test getBySystem() includes named derived SI units.
     */
    public function testGetBySystemIncludesNamedDerivedSiUnits(): void
    {
        $result = UnitService::getBySystem(UnitSystem::Si);
        $symbols = array_map(static fn (Unit $u) => $u->asciiSymbol, $result);

        $this->assertContains('N', $symbols);
        $this->assertContains('J', $symbols);
        $this->assertContains('W', $symbols);
        $this->assertContains('Pa', $symbols);
        $this->assertContains('Hz', $symbols);
    }

    /**
     * Test getBySystem() does not include Imperial units in SI results.
     */
    public function testGetBySystemExcludesOtherSystems(): void
    {
        $result = UnitService::getBySystem(UnitSystem::Si);
        $symbols = array_map(static fn (Unit $u) => $u->asciiSymbol, $result);

        $this->assertNotContains('ft', $symbols);
        $this->assertNotContains('lb', $symbols);
        $this->assertNotContains('mi', $symbols);
    }

    /**
     * Test getBySystem() returns Imperial units when loaded.
     */
    public function testGetBySystemReturnsImperialUnits(): void
    {
        $result = UnitService::getBySystem(UnitSystem::Imperial);
        $symbols = array_map(static fn (Unit $u) => $u->asciiSymbol, $result);

        $this->assertContains('ft', $symbols);
        $this->assertContains('lb', $symbols);
        $this->assertContains('mi', $symbols);
    }

    /**
     * Test getBySystem() returns different results for different systems.
     */
    public function testGetBySystemReturnsDifferentResultsPerSystem(): void
    {
        $si = UnitService::getBySystem(UnitSystem::Si);
        $imperial = UnitService::getBySystem(UnitSystem::Imperial);

        $siSymbols = array_map(static fn (Unit $u) => $u->asciiSymbol, $si);
        $imperialSymbols = array_map(static fn (Unit $u) => $u->asciiSymbol, $imperial);

        $this->assertNotEquals($siSymbols, $imperialSymbols);
    }

    /**
     * Test getBySystem() returns a sequential list (not associative).
     */
    public function testGetBySystemReturnsSequentialList(): void
    {
        $result = UnitService::getBySystem(UnitSystem::Si);

        $this->assertSame(array_values($result), $result);
    }

    #endregion

    #region Method getByQuantityType() tests.

    /**
     * Test getByQuantityType() returns units for a known quantity type.
     */
    public function testGetByQuantityTypeReturnsUnits(): void
    {
        $lengthType = QuantityTypeService::getByName('length');
        $this->assertNotNull($lengthType);

        $result = UnitService::getByQuantityType($lengthType);

        $this->assertNotEmpty($result);
        foreach ($result as $unit) {
            $this->assertInstanceOf(Unit::class, $unit);
        }
    }

    /**
     * Test getByQuantityType() returns only units with matching dimension.
     */
    public function testGetByQuantityTypeReturnsOnlyMatchingUnits(): void
    {
        $timeType = QuantityTypeService::getByName('time');
        $this->assertNotNull($timeType);

        $result = UnitService::getByQuantityType($timeType);

        foreach ($result as $unit) {
            $this->assertSame(
                $timeType->dimension,
                $unit->dimension,
                "Unit '$unit->name' has dimension '$unit->dimension', expected '$timeType->dimension'."
            );
        }
    }

    /**
     * Test getByQuantityType() includes expected units for time.
     */
    public function testGetByQuantityTypeIncludesExpectedUnits(): void
    {
        $timeType = QuantityTypeService::getByName('time');
        $this->assertNotNull($timeType);

        $result = UnitService::getByQuantityType($timeType);
        $symbols = array_map(static fn (Unit $u) => $u->asciiSymbol, $result);

        $this->assertContains('s', $symbols);
        $this->assertContains('min', $symbols);
        $this->assertContains('h', $symbols);
    }

    /**
     * Test getByQuantityType() returns a sequential list.
     */
    public function testGetByQuantityTypeReturnsSequentialList(): void
    {
        $lengthType = QuantityTypeService::getByName('length');
        $this->assertNotNull($lengthType);

        $result = UnitService::getByQuantityType($lengthType);

        $this->assertSame(array_values($result), $result);
    }

    #endregion

    #region Method getAllSymbols() tests.

    /**
     * Test getAllSymbols() returns array of strings.
     */
    public function testGetAllSymbolsReturnsArrayOfStrings(): void
    {
        $result = UnitService::getAllSymbols();

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertIsArray($result);

        foreach ($result as $symbol) {
            $this->assertIsString($symbol);
        }
    }

    /**
     * Test getAllSymbols() includes base unit symbols.
     */
    public function testGetAllSymbolsIncludesBaseSymbols(): void
    {
        $result = UnitService::getAllSymbols();

        $this->assertContains('m', $result);
        $this->assertContains('kg', $result);
        $this->assertContains('s', $result);
    }

    /**
     * Test getAllSymbols() includes prefixed symbols.
     */
    public function testGetAllSymbolsIncludesPrefixedSymbols(): void
    {
        $result = UnitService::getAllSymbols();

        // Meter should have metric prefixes.
        $this->assertContains('km', $result);   // kilo
        $this->assertContains('cm', $result);   // centi
        $this->assertContains('mm', $result);   // milli
        $this->assertContains('μm', $result);   // micro (Unicode)
        $this->assertContains('nm', $result);   // nano
    }

    /**
     * Test getAllSymbols() includes Unicode symbols.
     */
    public function testGetAllSymbolsIncludesUnicodeSymbols(): void
    {
        $result = UnitService::getAllSymbols();

        // Ohm has Unicode symbol.
        $this->assertContains('Ω', $result);
    }

    #endregion

    #region Data integrity tests.

    /**
     * Test all units have required properties.
     */
    public function testAllUnitsHaveRequiredProperties(): void
    {
        $all = UnitService::getAll();

        foreach ($all as $name => $unit) {
            $this->assertNotEmpty($unit->name, "Unit '$name' has empty name.");
            $this->assertNotEmpty($unit->systems, "Unit '$name' has empty systems.");
        }
    }

    /**
     * Test SI base units have correct dimensions.
     */
    public function testSiBaseUnitsHaveCorrectDimensions(): void
    {
        $expected = [
            'meter' => 'L',
            'gram' => 'M',
            'second' => 'T',
            'ampere' => 'I',
            'kelvin' => 'H',
            'mole' => 'N',
            'candela' => 'J',
        ];

        $all = UnitService::getAll();

        foreach ($expected as $name => $dimension) {
            $this->assertArrayHasKey($name, $all, "SI base unit '$name' not found.");
            $this->assertSame($dimension, $all[$name]->dimension, "Unit '$name' has wrong dimension.");
        }
    }

    /**
     * Test unit names are unique.
     */
    public function testUnitNamesAreUnique(): void
    {
        $all = UnitService::getAll();
        $names = array_keys($all);
        $uniqueNames = array_unique($names);

        $this->assertCount(count($names), $uniqueNames, 'Duplicate unit names found.');
    }

    /**
     * Test ASCII symbols are unique across all units.
     */
    public function testAsciiSymbolsAreUnique(): void
    {
        $all = UnitService::getAll();
        $symbols = array_map(static fn ($unit) => $unit->asciiSymbol, $all);
        $uniqueSymbols = array_unique($symbols);

        $this->assertCount(count($symbols), $uniqueSymbols, 'Duplicate ASCII symbols found.');
    }

    #endregion
}
