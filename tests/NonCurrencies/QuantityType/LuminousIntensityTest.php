<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\QuantityType;

use Mossy2100\Quantities\QuantityType\LuminousIntensity;
use Mossy2100\Quantities\Tests\NonCurrencies\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the LuminousIntensity quantity type.
 */
#[CoversClass(LuminousIntensity::class)]
final class LuminousIntensityTest extends TestCase
{
    use ArrayShapeTrait;

    #region Method getUnitDefinitions() tests.

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = LuminousIntensity::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    #endregion

    #region Method getConversionDefinitions() tests.

    /**
     * Test getConversionDefinitions() returns empty array.
     */
    public function testGetConversionDefinitionsReturnsEmptyArray(): void
    {
        $conversions = LuminousIntensity::getConversionDefinitions();

        $this->assertEmpty($conversions);
    }

    #endregion
}
