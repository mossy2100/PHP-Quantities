<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\Unit;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Internal\Converter;
use Mossy2100\Quantities\Quantity;
use Mossy2100\Quantities\Services\UnitService;
use Mossy2100\Quantities\Units\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the helper methods of Unit.
 */
#[CoversClass(Unit::class)]
final class UnitHelperTest extends TestCase
{
    #region Method tryExpand() tests.

    /**
     * Test tryExpand expands a named unit to base units.
     */
    public function testTryExpandExpandsNamedUnit(): void
    {
        $unit = UnitService::getBySymbol('N');

        $this->assertInstanceOf(Unit::class, $unit);

        $expansion = $unit->tryExpand();

        $this->assertInstanceOf(Quantity::class, $expansion);
        $this->assertSame(1.0, $expansion->value);
        $this->assertSame('kg*m/s2', $expansion->compoundUnit->asciiSymbol);
    }

    /**
     * Test tryExpand returns null for a base unit.
     */
    public function testTryExpandReturnsNullForBaseUnit(): void
    {
        $unit = UnitService::getBySymbol('m');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertNull($unit->tryExpand());
    }

    /**
     * Test tryExpand returns cached result on second call.
     */
    public function testTryExpandReturnsCachedExpansion(): void
    {
        $unit = UnitService::getBySymbol('N');
        $this->assertInstanceOf(Unit::class, $unit);

        $expansion1 = $unit->tryExpand();
        $expansion2 = $unit->tryExpand();

        $this->assertInstanceOf(Quantity::class, $expansion1);
        $this->assertSame($expansion1, $expansion2);
    }

    /**
     * Test tryExpand returns null when no conversions exist for the unit.
     */
    public function testTryExpandReturnsNullWhenNoConversionsExist(): void
    {
        // Create a non-base unit with no conversions registered.
        $unit = new Unit(
            name: 'no expand test',
            asciiSymbol: 'Xne',
            dimension: 'ML2T-2',
            systems: [UnitSystem::Si]
        );
        UnitService::add($unit);

        try {
            $this->assertNull($unit->tryExpand());
        } finally {
            UnitService::remove($unit);
            Converter::removeAllInstances();
        }
    }

    /**
     * Test tryExpand prefers unity expansion factor.
     */
    public function testTryExpandPrefersUnityFactor(): void
    {
        // Reset to ensure fresh Unit objects with no cached expansions.
        UnitService::reset();

        // J (joule) has a unity conversion to kg*m2/s2 (factor = 1).
        $unit = UnitService::getBySymbol('J');
        $this->assertInstanceOf(Unit::class, $unit);

        $expansion = $unit->tryExpand();

        $this->assertInstanceOf(Quantity::class, $expansion);
        $this->assertSame(1.0, $expansion->value);
        $this->assertSame('kg*m2/s2', $expansion->compoundUnit->asciiSymbol);
    }

    /**
     * Test tryExpand with a non-unity expansion factor.
     */
    public function testTryExpandWithNonUnityFactor(): void
    {
        UnitService::reset();

        // lbf expands to lb*ft/s2 with factor ≈ 32.174 (not 1.0).
        $unit = UnitService::getBySymbol('lbf');
        $this->assertInstanceOf(Unit::class, $unit);

        $expansion = $unit->tryExpand();

        $this->assertInstanceOf(Quantity::class, $expansion);
        $this->assertEqualsWithDelta(9.80665 / 0.3048, $expansion->value, 1e-4);
        $this->assertSame('lb*ft/s2', $expansion->compoundUnit->asciiSymbol);
    }

    #endregion
}
