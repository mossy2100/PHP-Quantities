<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\UnitTerm;

use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\QuantityType;
use Mossy2100\Quantities\Services\UnitService;
use Mossy2100\Quantities\Units\Unit;
use Mossy2100\Quantities\Units\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the properties of UnitTerm.
 */
#[CoversClass(UnitTerm::class)]
final class UnitTermPropertiesTest extends TestCase
{
    #region Property $asciiSymbol tests.

    /**
     * Test asciiSymbol property for base unit.
     */
    public function testAsciiSymbolPropertyForUnit(): void
    {
        $term = new UnitTerm('m');

        $this->assertSame('m', $term->asciiSymbol);
    }

    /**
     * Test asciiSymbol property with prefix.
     */
    public function testAsciiSymbolPropertyWithPrefix(): void
    {
        $term = new UnitTerm('m', 'k');

        $this->assertSame('km', $term->asciiSymbol);
    }

    /**
     * Test asciiSymbol property with exponent.
     */
    public function testAsciiSymbolPropertyWithExponent(): void
    {
        $term = new UnitTerm('m', null, 2);

        $this->assertSame('m2', $term->asciiSymbol);
    }

    /**
     * Test asciiSymbol property with prefix and exponent.
     */
    public function testAsciiSymbolPropertyWithPrefixAndExponent(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $this->assertSame('km2', $term->asciiSymbol);
    }

    /**
     * Test asciiSymbol property with negative exponent.
     */
    public function testAsciiSymbolPropertyWithNegativeExponent(): void
    {
        $term = new UnitTerm('s', null, -2);

        $this->assertSame('s-2', $term->asciiSymbol);
    }

    #endregion

    #region Property $unprefixedAsciiSymbol tests.

    /**
     * Test unprefixedAsciiSymbol property.
     */
    public function testUnprefixedAsciiSymbolProperty(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $this->assertSame('m2', $term->unprefixedAsciiSymbol);
    }

    #endregion

    #region Property $unexponentiatedAsciiSymbol tests.

    /**
     * Test unexponentiatedAsciiSymbol property.
     */
    public function testUnexponentiatedAsciiSymbolProperty(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $this->assertSame('km', $term->unexponentiatedAsciiSymbol);
    }

    #endregion

    #region Property $prefix tests.

    /**
     * Test prefixMultiplier property without prefix.
     */
    public function testPrefixMultiplierWithoutPrefix(): void
    {
        $term = new UnitTerm('m');

        $this->assertSame(1.0, $term->prefixMultiplier);
    }

    /**
     * Test prefixMultiplier property with kilo prefix.
     */
    public function testPrefixMultiplierWithKilo(): void
    {
        $term = new UnitTerm('m', 'k');

        $this->assertSame(1000.0, $term->prefixMultiplier);
    }

    /**
     * Test prefixMultiplier property with milli prefix.
     */
    public function testPrefixMultiplierWithMilli(): void
    {
        $term = new UnitTerm('m', 'm');

        $this->assertSame(0.001, $term->prefixMultiplier);
    }

    #endregion

    #region Property $multiplier tests.

    /**
     * Test multiplier property without prefix (exponent 1).
     */
    public function testMultiplierWithoutPrefix(): void
    {
        $term = new UnitTerm('m');

        $this->assertSame(1.0, $term->multiplier);
    }

    /**
     * Test multiplier property with prefix (exponent 1).
     */
    public function testMultiplierWithPrefix(): void
    {
        $term = new UnitTerm('m', 'k');

        $this->assertSame(1000.0, $term->multiplier);
    }

    /**
     * Test multiplier property with prefix and exponent.
     */
    public function testMultiplierWithPrefixAndExponent(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        // 1000^2 = 1,000,000
        $this->assertSame(1e6, $term->multiplier);
    }

    /**
     * Test multiplier property with prefix and negative exponent.
     */
    public function testMultiplierWithPrefixAndNegativeExponent(): void
    {
        $term = new UnitTerm('s', 'm', -2);

        // 0.001^-2 = 1,000,000
        $this->assertSame(1e6, $term->multiplier);
    }

    #endregion

    #region Property $dimension tests.

    /**
     * Test dimension property for base unit.
     */
    public function testDimensionPropertyForUnit(): void
    {
        $term = new UnitTerm('m');

        $this->assertSame('L', $term->dimension);
    }

    /**
     * Test dimension property with exponent.
     */
    public function testDimensionPropertyWithExponent(): void
    {
        $term = new UnitTerm('m', null, 2);

        $this->assertSame('L2', $term->dimension);
    }

    /**
     * Test dimension property with negative exponent.
     */
    public function testDimensionPropertyWithNegativeExponent(): void
    {
        $term = new UnitTerm('s', null, -2);

        $this->assertSame('T-2', $term->dimension);
    }

    #endregion

    #region Property $unicodeSymbol tests.

    /**
     * Test unicodeSymbol property directly.
     */
    public function testUnicodeSymbolProperty(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $this->assertSame('km²', $term->unicodeSymbol);
    }

    /**
     * Test unicodeSymbol property uses Unicode unit symbol.
     */
    public function testUnicodeSymbolPropertyUsesUnicodeUnitSymbol(): void
    {
        $term = new UnitTerm('ohm');

        $this->assertSame('Ω', $term->unicodeSymbol);
    }

    /**
     * Test unicodeSymbol property with prefix and exponent on Unicode unit.
     */
    public function testUnicodeSymbolPropertyWithPrefixAndExponentOnUnicodeUnit(): void
    {
        $term = new UnitTerm('ohm', 'k', -1);

        $this->assertSame('kΩ⁻¹', $term->unicodeSymbol);
    }

    #endregion

    #region Property $quantityType tests.

    /**
     * Test quantityType property returns QuantityType for a registered dimension.
     */
    public function testQuantityTypePropertyReturnsQuantityTypeForRegisteredDimension(): void
    {
        $term = new UnitTerm('m');

        $quantityType = $term->quantityType;
        $this->assertInstanceOf(QuantityType::class, $quantityType);
        $this->assertSame('length', $quantityType->name);
    }

    /**
     * Test quantityType property returns null for an unregistered dimension.
     */
    public function testQuantityTypePropertyReturnsNullForUnregisteredDimension(): void
    {
        // Create a unit with a dimension that has no registered quantity type.
        $unit = new Unit(
            name: 'unregistered test',
            asciiSymbol: 'Xur',
            dimension: 'L5T3',
            systems: [UnitSystem::Custom]
        );
        UnitService::add($unit);

        try {
            $term = new UnitTerm($unit);
            $this->assertNull($term->quantityType);
        } finally {
            UnitService::remove($unit);
        }
    }

    #endregion
}
