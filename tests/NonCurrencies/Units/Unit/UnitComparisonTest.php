<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\Unit;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Units\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for the comparison methods of Unit.
 */
#[CoversClass(Unit::class)]
final class UnitComparisonTest extends TestCase
{
    #region Method equal() tests.

    /**
     * Test equal returns true for same Unit instance.
     */
    public function testEqualReturnsTrueForSameInstance(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si]
        );

        $this->assertTrue($unit->equal($unit));
    }

    /**
     * Test equal returns true for different instances with same name.
     */
    public function testEqualReturnsTrueForSameName(): void
    {
        $unit1 = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si]
        );
        $unit2 = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si]
        );

        $this->assertTrue($unit1->equal($unit2));
    }

    /**
     * Test equal returns false for different unit names.
     */
    public function testEqualReturnsFalseForDifferentNames(): void
    {
        $meter = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si]
        );
        $foot = new Unit(
            name: 'foot',
            asciiSymbol: 'ft',
            dimension: 'L',
            systems: [UnitSystem::Imperial]
        );

        $this->assertFalse($meter->equal($foot));
    }

    /**
     * Test equal returns false for different types.
     */
    public function testEqualReturnsFalseForDifferentTypes(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si]
        );

        $this->assertFalse($unit->equal('m'));
        $this->assertFalse($unit->equal(1));
        $this->assertFalse($unit->equal(null));
        $this->assertFalse($unit->equal(new stdClass()));
    }

    #endregion
}
