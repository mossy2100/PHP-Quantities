<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\UnitTerm;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\Internal\Converter;
use Mossy2100\Quantities\Services\UnitService;
use Mossy2100\Quantities\Units\Unit;
use Mossy2100\Quantities\Units\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the helper methods of UnitTerm.
 */
#[CoversClass(UnitTerm::class)]
final class UnitTermHelperTest extends TestCase
{
    #region Method tryExpand() tests.

    /**
     * Test tryExpand returns null for a base unit term.
     */
    public function testTryExpandReturnsNullForBaseUnit(): void
    {
        $term = new UnitTerm('m');

        $this->assertNull($term->tryExpand());
    }

    /**
     * Test tryExpand expands a named compound unit.
     */
    public function testTryExpandExpandsNamedUnit(): void
    {
        // N (newton) expands to kg*m/s2.
        $term = new UnitTerm('N');
        $expansion = $term->tryExpand();

        $this->assertNotNull($expansion);
        $this->assertSame(1.0, $expansion->value);
        $this->assertSame('kg*m/s2', $expansion->compoundUnit->asciiSymbol);
    }

    /**
     * Test tryExpand with a prefixed named unit.
     */
    public function testTryExpandWithPrefixedUnit(): void
    {
        // kN = 1000 * kg*m/s2.
        $term = new UnitTerm('N', 'k');
        $expansion = $term->tryExpand();

        $this->assertNotNull($expansion);
        $this->assertEqualsWithDelta(1000.0, $expansion->value, 1e-10);
        $this->assertSame('kg*m/s2', $expansion->compoundUnit->asciiSymbol);
    }

    /**
     * Test tryExpand with an exponent.
     */
    public function testTryExpandWithExponent(): void
    {
        // N2 = (kg*m/s2)^2 = kg2*m2/s4.
        $term = new UnitTerm('N', null, 2);
        $expansion = $term->tryExpand();

        $this->assertNotNull($expansion);
        $this->assertSame(1.0, $expansion->value);
        $this->assertSame('kg2*m2/s4', $expansion->compoundUnit->asciiSymbol);
    }

    /**
     * Test tryExpand with prefix and exponent.
     */
    public function testTryExpandWithPrefixAndExponent(): void
    {
        // kN2 = (1000 * kg*m/s2)^2 = 1e6 * kg2*m2/s4.
        $term = new UnitTerm('N', 'k', 2);
        $expansion = $term->tryExpand();

        $this->assertNotNull($expansion);
        $this->assertEqualsWithDelta(1e6, $expansion->value, 1e-4);
        $this->assertSame('kg2*m2/s4', $expansion->compoundUnit->asciiSymbol);
    }

    /**
     * Test tryExpand returns cached expansion on second call.
     */
    public function testTryExpandReturnsCachedExpansion(): void
    {
        $term = new UnitTerm('N');

        $expansion1 = $term->tryExpand();
        $expansion2 = $term->tryExpand();

        $this->assertNotNull($expansion1);
        $this->assertSame($expansion1, $expansion2);
    }

    /**
     * Test tryExpand returns null for a unit with no known expansion.
     */
    public function testTryExpandReturnsNullForUnexpandableUnit(): void
    {
        // Base units with an exponent are still base.
        $term = new UnitTerm('m', null, 2);

        $this->assertNull($term->tryExpand());
    }

    /**
     * Test tryExpand returns null for a non-base unit with no expansion path.
     */
    public function testTryExpandReturnsNullForNonBaseUnitWithNoExpansion(): void
    {
        // Create a custom unit with a compound dimension (non-base) and no conversions.
        $unit = new Unit(
            name: 'unexpandable test',
            asciiSymbol: 'Xut',
            dimension: 'ML2T-2',
            systems: [UnitSystem::Si]
        );
        UnitService::add($unit);

        try {
            $term = new UnitTerm($unit);

            // The unit is non-base (compound dimension) but has no expansion conversions.
            $this->assertNull($term->tryExpand());
        } finally {
            UnitService::remove($unit);
            Converter::removeAllInstances();
        }
    }

    #endregion
}
