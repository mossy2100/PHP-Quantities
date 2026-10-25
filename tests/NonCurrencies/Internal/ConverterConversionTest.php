<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Internal;

use DomainException;
use Mossy2100\Quantities\Internal\Converter;
use Mossy2100\Quantities\Quantity;
use Mossy2100\Quantities\Services\QuantityTypeService;
use Mossy2100\Quantities\Services\UnitService;
use Mossy2100\Quantities\Tests\NonCurrencies\Fixtures\InvalidOffsetQuantity;
use Mossy2100\Quantities\Tests\NonCurrencies\Fixtures\OffsetQuantity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the conversion methods of Converter, with offset units from fixture quantity types.
 */
#[CoversClass(Converter::class)]
final class ConverterConversionTest extends TestCase
{
    #region Setup

    protected function setUp(): void
    {
        // Register the fixture quantity types, then rebuild the registries so their units are loaded.
        QuantityTypeService::add('offset test', 'L4', OffsetQuantity::class);
        QuantityTypeService::add('invalid offset test', 'M4', InvalidOffsetQuantity::class);
        UnitService::reset();
        Converter::removeAllInstances();
    }

    protected function tearDown(): void
    {
        QuantityTypeService::remove('offset test');
        QuantityTypeService::remove('invalid offset test');
        UnitService::reset();
        Converter::removeAllInstances();
    }

    #endregion

    #region Method convert() tests.

    /**
     * Test converting from an offset unit to its zero unit adds the offset.
     */
    public function testConvertOffsetUnitToZeroUnit(): void
    {
        $this->assertSame(15.0, Quantity::convert(5, 'otu', 'ztu'));
    }

    /**
     * Test converting from a zero unit to its offset unit subtracts the offset.
     */
    public function testConvertZeroUnitToOffsetUnit(): void
    {
        $this->assertSame(5.0, Quantity::convert(15, 'ztu', 'otu'));
    }

    /**
     * Test converting from an offset unit to another unit applies the offset, then the factor.
     */
    public function testConvertOffsetUnitToOtherUnit(): void
    {
        $this->assertSame(30.0, Quantity::convert(5, 'otu', 'm4'));
        $this->assertSame(5.0, Quantity::convert(30, 'm4', 'otu'));
    }

    /**
     * Test offsets aren't applied when the offset unit is part of a compound unit.
     */
    public function testConvertCompoundUnitIgnoresOffset(): void
    {
        $this->assertSame(5.0, Quantity::convert(5, 'otu/s', 'ztu/s'));
    }

    /**
     * Test an offset definition whose unit is a different size to its zero unit throws.
     */
    public function testConvertThrowsForInvalidOffsetDefinition(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid offset definition for 'bou'. It must be the same size as 'bzu'.");

        Quantity::convert(1, 'bou', 'bzu');
    }

    #endregion
}
