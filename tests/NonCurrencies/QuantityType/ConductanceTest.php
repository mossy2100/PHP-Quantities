<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\QuantityType;

use Mossy2100\Quantities\QuantityType\Conductance;
use Mossy2100\Quantities\Tests\NonCurrencies\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Conductance quantity type.
 */
#[CoversClass(Conductance::class)]
final class ConductanceTest extends TestCase
{
    use ArrayShapeTrait;

    #region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = Conductance::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getConversionDefinitions() returns valid conversion definitions.
     */
    public function testGetConversionDefinitionsReturnsValidArray(): void
    {
        $conversions = Conductance::getConversionDefinitions();

        $this->assertValidConversionDefinitionsShape($conversions);
    }

    #endregion
}
