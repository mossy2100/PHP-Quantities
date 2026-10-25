<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\QuantityType;

use Mossy2100\Quantities\QuantityType\Capacitance;
use Mossy2100\Quantities\Tests\NonCurrencies\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Capacitance quantity type.
 */
#[CoversClass(Capacitance::class)]
final class CapacitanceTest extends TestCase
{
    use ArrayShapeTrait;

    #region Method getUnitDefinitions() tests.

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = Capacitance::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    #endregion

    #region Method getConversionDefinitions() tests.

    /**
     * Test getConversionDefinitions() returns valid conversion definitions.
     */
    public function testGetConversionDefinitionsReturnsValidArray(): void
    {
        $conversions = Capacitance::getConversionDefinitions();

        $this->assertValidConversionDefinitionsShape($conversions);
    }

    #endregion
}
