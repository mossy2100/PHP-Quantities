<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Services\UnitService;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Services\UnitService;
use Mossy2100\Quantities\Units\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the inspection methods of UnitService.
 */
#[CoversClass(UnitService::class)]
final class UnitServiceInspectionTest extends TestCase
{
    #region Method has() tests.

    /**
     * Test has() returns true for existing unit by name string.
     */
    public function testHasReturnsTrueForExistingUnitByName(): void
    {
        $this->assertTrue(UnitService::has('meter'));
    }

    /**
     * Test has() returns true for existing unit by Unit object.
     */
    public function testHasReturnsTrueForExistingUnitByObject(): void
    {
        $unit = UnitService::getByName('meter');
        $this->assertNotNull($unit);

        $this->assertTrue(UnitService::has($unit));
    }

    /**
     * Test has() returns true for other SI base units.
     */
    public function testHasReturnsTrueForOtherSiUnits(): void
    {
        $this->assertTrue(UnitService::has('gram'));
        $this->assertTrue(UnitService::has('second'));
        $this->assertTrue(UnitService::has('ampere'));
        $this->assertTrue(UnitService::has('kelvin'));
    }

    /**
     * Test has() returns false for non-existing unit.
     */
    public function testHasReturnsFalseForNonExistingUnit(): void
    {
        $this->assertFalse(UnitService::has('nonexistent_unit_xyz'));
    }

    /**
     * Test has() is case sensitive.
     */
    public function testHasIsCaseSensitive(): void
    {
        $this->assertTrue(UnitService::has('meter'));
        $this->assertFalse(UnitService::has('Meter'));
        $this->assertFalse(UnitService::has('METER'));
    }

    #endregion

    #region Method count() tests.

    /**
     * Test count() returns a positive integer for the default registry.
     */
    public function testCountReturnsPositiveInteger(): void
    {
        $this->assertGreaterThan(0, UnitService::count());
    }

    /**
     * Test count() increases after adding a unit.
     */
    public function testCountIncreasesAfterAdd(): void
    {
        $countBefore = UnitService::count();

        $unit = new Unit(
            name: 'counttest',
            asciiSymbol: 'cnt',
            dimension: 'L',
            systems: [UnitSystem::Custom]
        );

        try {
            UnitService::add($unit);
            $this->assertSame($countBefore + 1, UnitService::count());
        } finally {
            UnitService::remove($unit);
        }
    }

    /**
     * Test count() decreases after removing a unit.
     */
    public function testCountDecreasesAfterRemove(): void
    {
        $unit = new Unit(
            name: 'countremovetest',
            asciiSymbol: 'crt',
            dimension: 'L',
            systems: [UnitSystem::Custom]
        );
        UnitService::add($unit);
        $countBefore = UnitService::count();

        UnitService::remove($unit);

        $this->assertSame($countBefore - 1, UnitService::count());
    }

    /**
     * Test count() returns zero after removeAll().
     */
    public function testCountReturnsZeroAfterRemoveAll(): void
    {
        try {
            UnitService::removeAll();
            $this->assertSame(0, UnitService::count());
        } finally {
            UnitService::reset();
        }
    }

    #endregion
}
