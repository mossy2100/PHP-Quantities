<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\QuantityType;

use Mossy2100\Quantities\QuantityType\Density;
use Mossy2100\Quantities\Tests\NonCurrencies\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Density quantity type.
 */
#[CoversClass(Density::class)]
final class DensityTest extends TestCase
{
    use ArrayShapeTrait;

    #region Method getUnitDefinitions() tests.

    /**
     * Test getUnitDefinitions() returns empty array (not overridden).
     */
    public function testGetUnitDefinitionsReturnsEmptyArray(): void
    {
        $units = Density::getUnitDefinitions();

        $this->assertEmpty($units);
    }

    #endregion

    #region Method getConversionDefinitions() tests.

    /**
     * Test getConversionDefinitions() returns empty array (not overridden).
     */
    public function testGetConversionDefinitionsReturnsEmptyArray(): void
    {
        $conversions = Density::getConversionDefinitions();

        $this->assertEmpty($conversions);
    }

    #endregion
}
