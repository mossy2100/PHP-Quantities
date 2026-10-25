<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Services\UnitService;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Services\UnitService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the loading methods of UnitService.
 */
#[CoversClass(UnitService::class)]
final class UnitServiceLoadingTest extends TestCase
{
    #region Method loadSystem() tests.

    /**
     * Test loadSystem() adds units from the specified system.
     */
    public function testLoadSystemAddsUnits(): void
    {
        try {
            UnitService::removeAll();
            UnitService::loadSystem(UnitSystem::Imperial);

            $this->assertTrue(UnitService::has('foot'));
            $this->assertTrue(UnitService::has('mile'));
        } finally {
            UnitService::reset();
        }
    }

    /**
     * Test loadSystem() does not add duplicate units when called twice.
     */
    public function testLoadSystemDoesNotDuplicateUnits(): void
    {
        // SI is auto-loaded during init(). Loading it again should be a no-op.
        $countBefore = UnitService::count();

        UnitService::loadSystem(UnitSystem::Si);

        $this->assertSame($countBefore, UnitService::count());
    }

    /**
     * Test loadSystem() loads units from an unloaded system.
     */
    public function testLoadSystemLoadsUnitsFromSystem(): void
    {
        try {
            UnitService::removeAll();
            UnitService::loadSystem(UnitSystem::Nautical);

            $this->assertTrue(UnitService::has('nautical mile'));
        } finally {
            UnitService::reset();
        }
    }

    #endregion

    #region Method loadAll() tests.

    /**
     * Test loadAll() makes Imperial and US Customary units available.
     */
    public function testLoadAllIncludesNonDefaultUnits(): void
    {
        try {
            UnitService::removeAll();
            UnitService::loadAll();

            $this->assertTrue(UnitService::has('foot'));
            $this->assertTrue(UnitService::has('mile'));
            $this->assertTrue(UnitService::has('meter'));
        } finally {
            UnitService::reset();
        }
    }

    #endregion
}
