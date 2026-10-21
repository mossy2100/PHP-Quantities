<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\QuantityType;

use Mossy2100\Quantities\QuantityType\CatalyticActivity;
use Mossy2100\Quantities\Tests\NonCurrencies\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the CatalyticActivity quantity type.
 */
#[CoversClass(CatalyticActivity::class)]
final class CatalyticActivityTest extends TestCase
{
    use ArrayShapeTrait;

    #region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = CatalyticActivity::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getConversionDefinitions() returns valid conversion definitions.
     */
    public function testGetConversionDefinitionsReturnsValidArray(): void
    {
        $conversions = CatalyticActivity::getConversionDefinitions();

        $this->assertValidConversionDefinitionsShape($conversions);
    }

    #endregion
}
