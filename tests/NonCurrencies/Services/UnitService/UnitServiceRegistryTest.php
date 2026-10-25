<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Services\UnitService;

use DomainException;
use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Exceptions\UnknownUnitException;
use Mossy2100\Quantities\Services\PrefixService;
use Mossy2100\Quantities\Services\UnitService;
use Mossy2100\Quantities\Units\Unit;
use Mossy2100\Quantities\Units\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the registry methods of UnitService.
 */
#[CoversClass(UnitService::class)]
final class UnitServiceRegistryTest extends TestCase
{
    #region Method add() tests.

    /**
     * Test add() creates a new unit and returns true.
     */
    public function testAddCreatesNewUnit(): void
    {
        $unit = new Unit(
            name: 'testunitadd',
            asciiSymbol: 'tua',
            dimension: 'L',
            systems: [UnitSystem::Custom],
            unicodeSymbol: 'τ'
        );

        try {
            $result = UnitService::add($unit);

            $this->assertTrue($result);
            $this->assertArrayHasKey($unit->name, UnitService::getAll());
            $this->assertSame('L', UnitService::getAll()[$unit->name]->dimension);
        } finally {
            UnitService::remove($unit);
        }
    }

    /**
     * Test add() throws when a symbol conflicts with an existing unit's symbol.
     */
    public function testAddThrowsForDuplicateSymbol(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Cannot add unit 'newunitohm'. Its symbol 'Ω' is already used by another unit.");

        UnitService::add(new Unit(
            name: 'newunitohm',
            asciiSymbol: 'nuo',
            dimension: 'T-3L2MI-2',
            systems: [UnitSystem::Custom],
            unicodeSymbol: 'Ω'  // Conflicts with ohm's Unicode symbol.
        ));
    }

    /**
     * Test add() with all optional parameters.
     */
    public function testAddWithAllParameters(): void
    {
        $unit = new Unit(
            name: 'fullunitparams',
            asciiSymbol: 'fup',
            dimension: 'L',
            systems: [UnitSystem::Custom],
            prefixGroup: PrefixService::GROUP_METRIC,
            unicodeSymbol: 'φ'
        );

        try {
            UnitService::add($unit);

            $retrieved = UnitService::getAll()[$unit->name];
            $this->assertSame('fup', $retrieved->asciiSymbol);
            $this->assertContains(UnitSystem::Custom, $retrieved->systems);
            $this->assertSame(PrefixService::GROUP_METRIC, $retrieved->prefixGroup);
        } finally {
            UnitService::remove($unit);
        }
    }

    /**
     * Test add() into an empty registry (after removeAll) does not auto-initialize.
     */
    public function testAddIntoEmptyRegistry(): void
    {
        UnitService::removeAll();

        try {
            $unit = new Unit(
                name: 'onlyunit',
                asciiSymbol: 'onu',
                dimension: 'L',
                systems: [UnitSystem::Custom]
            );

            UnitService::add($unit);

            $this->assertSame(1, UnitService::count());
        } finally {
            UnitService::reset();
        }
    }

    /**
     * Test add() auto-initializes the registry when it is null (after reset).
     */
    public function testAddAutoInitializesNullRegistry(): void
    {
        UnitService::reset();

        $unit = new Unit(
            name: 'autoinitunit',
            asciiSymbol: 'aiu',
            dimension: 'L',
            systems: [UnitSystem::Custom]
        );

        try {
            UnitService::add($unit);

            // The registry should have been initialized with default units plus our new one.
            $this->assertTrue(UnitService::has('meter'));
            $this->assertTrue(UnitService::has($unit->name));
        } finally {
            UnitService::remove($unit);
        }
    }

    /**
     * Test add() replaces an existing unit when replaceExisting is true and returns true.
     */
    public function testAddReplacesExistingUnit(): void
    {
        $name = 'replacetest';

        $unit = new Unit(
            name: $name,
            asciiSymbol: 'rpt',
            dimension: 'L',
            systems: [UnitSystem::Custom]
        );

        try {
            UnitService::add($unit);
            $this->assertSame('rpt', UnitService::getAll()[$name]->asciiSymbol);

            // Replace with different symbol.
            $replacement = new Unit(
                name: $name,
                asciiSymbol: 'rpx',
                dimension: 'L',
                systems: [UnitSystem::Custom]
            );
            $result = UnitService::add($replacement, true);

            $this->assertTrue($result);
            $this->assertSame('rpx', UnitService::getAll()[$name]->asciiSymbol);
            $this->assertSame($replacement, UnitService::getBySymbol('rpx'));
            $this->assertNull(UnitService::getBySymbol('rpt'));
        } finally {
            UnitService::remove($name);
        }
    }

    /**
     * Test add() doesn't register any of a unit's symbols when one of them conflicts.
     */
    public function testAddWithConflictingSymbolRegistersNoSymbols(): void
    {
        // The unit's Unicode symbol 'm' conflicts with the meter, but its ASCII symbol 'cft' doesn't.
        $unit = new Unit(
            name: 'conflicttest',
            asciiSymbol: 'cft',
            dimension: 'L',
            systems: [UnitSystem::Custom],
            unicodeSymbol: 'm'
        );

        try {
            UnitService::add($unit);
            $this->fail('Expected DomainException.');
        } catch (DomainException) {
            $this->assertNull(UnitService::getBySymbol('cft'));
            $this->assertSame('meter', UnitService::getBySymbol('m')?->name);
        }
    }

    /**
     * Test add() skips an existing unit when replaceExisting is false and returns false.
     */
    public function testAddSkipsExistingUnit(): void
    {
        $name = 'skiptest';

        $unit = new Unit(
            name: $name,
            asciiSymbol: 'skt',
            dimension: 'L',
            systems: [UnitSystem::Custom]
        );

        try {
            UnitService::add($unit);

            $result = UnitService::add(new Unit(
                name: $name,
                asciiSymbol: 'skx',
                dimension: 'L',
                systems: [UnitSystem::Custom]
            ));

            $this->assertFalse($result);
            $this->assertSame('skt', UnitService::getAll()[$name]->asciiSymbol);
        } finally {
            UnitService::remove($name);
        }
    }

    #endregion

    #region Method addFromDefinition() tests.

    /**
     * Test addFromDefinition() creates a unit from a definition array.
     */
    public function testAddFromDefinitionCreatesUnit(): void
    {
        try {
            UnitService::addFromDefinition('testdefunit', [
                'asciiSymbol' => 'tdu',
                'dimension' => 'L',
                'systems' => [UnitSystem::Custom],
            ]);

            $unit = UnitService::getByName('testdefunit');
            $this->assertInstanceOf(Unit::class, $unit);
            $this->assertSame('tdu', $unit->asciiSymbol);
            $this->assertSame('L', $unit->dimension);
        } finally {
            UnitService::remove('testdefunit');
        }
    }

    /**
     * Test addFromDefinition() with all optional fields.
     */
    public function testAddFromDefinitionWithAllFields(): void
    {
        try {
            UnitService::addFromDefinition('fulldefunit', [
                'asciiSymbol' => 'fdu',
                'dimension' => 'T',
                'systems' => [UnitSystem::Custom],
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'unicodeSymbol' => 'ψ',
                'alternateSymbol' => '#',
            ]);

            $unit = UnitService::getByName('fulldefunit');
            $this->assertInstanceOf(Unit::class, $unit);
            $this->assertSame(PrefixService::GROUP_METRIC, $unit->prefixGroup);

            // Unicode and alternate symbols should be in the symbols map.
            $this->assertIsArray($unit->symbols);
            $this->assertArrayHasKey('ψ', $unit->symbols);
            $this->assertArrayHasKey('#', $unit->symbols);
        } finally {
            UnitService::remove('fulldefunit');
        }
    }

    #endregion

    #region Method remove() tests.

    /**
     * Test remove() removes a unit by Unit object.
     */
    public function testRemoveByUnitObject(): void
    {
        $unit = new Unit(
            name: 'removetest',
            asciiSymbol: 'rmt',
            dimension: 'L',
            systems: [UnitSystem::Custom]
        );
        UnitService::add($unit);
        $this->assertTrue(UnitService::has('removetest'));

        UnitService::remove($unit);

        $this->assertFalse(UnitService::has('removetest'));
        $this->assertNull(UnitService::getBySymbol('rmt'));
    }

    /**
     * Test remove() frees the unit's symbols, so another unit can use them.
     */
    public function testRemoveFreesSymbols(): void
    {
        $first = new Unit(name: 'freetestfirst', asciiSymbol: 'frt', dimension: 'L', systems: [UnitSystem::Custom]);
        UnitService::add($first);
        UnitService::remove($first);

        $second = new Unit(name: 'freetestsecond', asciiSymbol: 'frt', dimension: 'L', systems: [UnitSystem::Custom]);
        try {
            $this->assertTrue(UnitService::add($second));
            $this->assertSame($second, UnitService::getBySymbol('frt'));
        } finally {
            UnitService::remove($second);
        }
    }

    /**
     * Test remove() clears the parse caches, so a removed unit can't still be parsed.
     */
    public function testRemoveClearsParseCaches(): void
    {
        try {
            // Parse the unit first, so it's cached.
            Unit::parse('ft');
            UnitTerm::parse('ft');

            UnitService::remove('foot');

            $this->assertNull(UnitService::getBySymbol('ft'));
            foreach ([Unit::parse(...), UnitTerm::parse(...)] as $parse) {
                try {
                    $parse('ft');
                    $this->fail('Expected UnknownUnitException.');
                } catch (UnknownUnitException $e) {
                    $this->assertSame("Unknown unit: 'ft'.", $e->getMessage());
                }
            }
        } finally {
            UnitService::reset();
        }
    }

    /**
     * Test remove() removes a unit by name string.
     */
    public function testRemoveByNameString(): void
    {
        $unit = new Unit(
            name: 'removestringtest',
            asciiSymbol: 'rst',
            dimension: 'L',
            systems: [UnitSystem::Custom]
        );
        UnitService::add($unit);
        $this->assertTrue(UnitService::has('removestringtest'));

        UnitService::remove('removestringtest');

        $this->assertFalse(UnitService::has('removestringtest'));
    }

    /**
     * Test remove() does nothing for non-existent unit.
     */
    public function testRemoveDoesNothingForNonExistent(): void
    {
        $countBefore = UnitService::count();

        UnitService::remove('nonexistent_unit_xyz');

        $this->assertSame($countBefore, UnitService::count());
    }

    /**
     * Test remove() handles uninitialized (null) registry gracefully.
     */
    public function testRemoveHandlesUninitializedRegistry(): void
    {
        UnitService::reset();

        // remove() should return early without triggering init().
        UnitService::remove('meter');

        // Re-initialize by accessing the registry — meter should still exist.
        $result = UnitService::getByName('meter');
        $this->assertInstanceOf(Unit::class, $result);
    }

    #endregion

    #region Method removeAll() tests.

    /**
     * Test removeAll() empties the registry.
     */
    public function testRemoveAllEmptiesRegistry(): void
    {
        try {
            UnitService::removeAll();

            $this->assertSame(0, UnitService::count());
            $this->assertEmpty(UnitService::getAll());
            $this->assertEmpty(UnitService::getAllSymbols());
            $this->assertNull(UnitService::getBySymbol('m'));
        } finally {
            UnitService::reset();
        }
    }

    /**
     * Test removeAll() does not trigger re-initialization on next access.
     */
    public function testRemoveAllDoesNotReinitialize(): void
    {
        try {
            UnitService::removeAll();

            // Accessing getAll() should NOT re-initialize — it should stay empty.
            $this->assertEmpty(UnitService::getAll());
        } finally {
            UnitService::reset();
        }
    }

    #endregion

    #region Method reset() tests.

    /**
     * Test reset() followed by access still includes default units.
     */
    public function testResetStillHasDefaultUnits(): void
    {
        UnitService::reset();

        $this->assertTrue(UnitService::has('meter'));
        $this->assertTrue(UnitService::has('second'));
        $this->assertTrue(UnitService::has('gram'));
    }

    #endregion
}
