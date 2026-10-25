<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\Unit;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Services\PrefixService;
use Mossy2100\Quantities\Services\UnitService;
use Mossy2100\Quantities\Units\Prefix;
use Mossy2100\Quantities\Units\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the inspection methods of Unit.
 */
#[CoversClass(Unit::class)]
final class UnitInspectionTest extends TestCase
{
    #region Method acceptsPrefix() tests.

    /**
     * Test acceptsPrefix returns true for prefixes in the unit's group.
     */
    public function testAcceptsPrefixReturnsTrueForValidPrefix(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si],
            prefixGroup: PrefixService::GROUP_METRIC
        );

        $this->assertTrue($unit->acceptsPrefix(self::prefix('k')));
        $this->assertTrue($unit->acceptsPrefix(self::prefix('m')));
        $this->assertTrue($unit->acceptsPrefix(self::prefix('c')));
        $this->assertTrue($unit->acceptsPrefix(self::prefix('M')));
        $this->assertTrue($unit->acceptsPrefix(self::prefix('G')));
    }

    /**
     * Test acceptsPrefix returns true for a Unicode-symbol prefix (micro).
     */
    public function testAcceptsPrefixReturnsTrueForUnicodePrefix(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si],
            prefixGroup: PrefixService::GROUP_METRIC
        );

        $this->assertTrue($unit->acceptsPrefix(self::prefix("\u{00B5}")));
    }

    /**
     * Test acceptsPrefix returns false for a prefix outside the unit's group.
     */
    public function testAcceptsPrefixReturnsFalseForPrefixOutsideGroup(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si],
            prefixGroup: PrefixService::GROUP_METRIC
        );

        // Binary prefix not in METRIC group.
        $this->assertFalse($unit->acceptsPrefix(self::prefix('Ki')));
    }

    /**
     * Test acceptsPrefix returns false when no prefixes allowed.
     */
    public function testAcceptsPrefixReturnsFalseWhenNoPrefixesAllowed(): void
    {
        $unit = new Unit(
            name: 'hectare',
            asciiSymbol: 'ha',
            dimension: 'L2',
            systems: [UnitSystem::SiAccepted]
        );

        $this->assertFalse($unit->acceptsPrefix(self::prefix('k')));
    }

    #endregion

    #region Method belongsToSystem() tests.

    /**
     * Test belongsToSystem returns true for system the unit belongs to.
     */
    public function testBelongsToSystemReturnsTrueForMatchingSystem(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si]
        );

        $this->assertTrue($unit->belongsToSystem(UnitSystem::Si));
    }

    /**
     * Test belongsToSystem returns false for system the unit doesn't belong to.
     */
    public function testBelongsToSystemReturnsFalseForNonMatchingSystem(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si]
        );

        $this->assertFalse($unit->belongsToSystem(UnitSystem::Imperial));
    }

    /**
     * Test belongsToSystem with unit belonging to multiple systems.
     */
    public function testBelongsToSystemWithMultipleSystems(): void
    {
        $unit = new Unit(
            name: 'second',
            asciiSymbol: 's',
            dimension: 'T',
            systems: [UnitSystem::Si, UnitSystem::Imperial]
        );

        $this->assertTrue($unit->belongsToSystem(UnitSystem::Si));
        $this->assertTrue($unit->belongsToSystem(UnitSystem::Imperial));
    }

    #endregion

    #region Method isSi() tests.

    /**
     * Test isSi returns true for SI unit.
     */
    public function testIsSiReturnsTrueForSiUnit(): void
    {
        $unit = UnitService::getBySymbol('m');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertTrue($unit->isSi());
    }

    /**
     * Test isSi returns false for Imperial unit.
     */
    public function testIsSiReturnsFalseForImperialUnit(): void
    {
        $unit = UnitService::getBySymbol('ft');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertFalse($unit->isSi());
    }

    /**
     * Test isSi returns false for SIAccepted unit that is not SI.
     */
    public function testIsSiReturnsFalseForSiAcceptedOnly(): void
    {
        $unit = UnitService::getBySymbol('ha');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertFalse($unit->isSi());
    }

    #endregion

    #region Method isBase() tests.

    /**
     * Test isBase returns true for unit with single dimension term.
     */
    public function testIsBaseReturnsTrueForSingleDimensionTerm(): void
    {
        $unit = UnitService::getBySymbol('m');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertTrue($unit->isBase());
    }

    /**
     * Test isBase returns false for unit with multiple dimension terms.
     */
    public function testIsBaseReturnsFalseForMultipleDimensionTerms(): void
    {
        // Newton has dimension MLT-2 (3 terms).
        $unit = UnitService::getBySymbol('N');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertFalse($unit->isBase());
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
