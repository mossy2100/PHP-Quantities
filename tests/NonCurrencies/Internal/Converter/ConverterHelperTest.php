<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Internal\Converter;

use Mossy2100\Quantities\Internal\Converter;
use Mossy2100\Quantities\Units\Conversion;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the helper methods for adding unit terms of Converter.
 */
#[CoversClass(Converter::class)]
class ConverterHelperTest extends TestCase
{
    #region Method addMerged() tests.

    /**
     * Test that addMerged is triggered when finding conversion with mergeable units.
     *
     * This covers addMerged() by going through findConversion() with a mergeable unit.
     */
    public function testGetConversionTriggersMergeForMergeableUnits(): void
    {
        $converter = Converter::getInstance('L2');

        // m*ft has two length units that should be merged.
        // Getting a conversion should trigger addMerged internally.
        $conversion = $converter->findConversion('m*ft', 'm2');

        $this->assertInstanceOf(Conversion::class, $conversion);
        // 1 m*ft = 0.3048 m² (since 1 ft = 0.3048 m)
        $this->assertEqualsWithDelta(0.3048, $conversion->factor->value, 1e-4);
    }

    /**
     * Test that addExpanded is triggered for compound units during findConversion.
     *
     * When a non-base compound unit like N*m is first encountered, its expansion
     * (N → kg*m/s2, so N*m → kg*m2/s2) is discovered and added as a new conversion.
     */
    public function testFindConversionTriggersAddExpanded(): void
    {
        Converter::removeAllInstances();

        // N*m is not in the energy (ML2T-2) conversion definitions, but N can be expanded
        // to kg*m/s2, so N*m expands to kg*m2/s2 = J. This triggers addExpanded().
        $converter = Converter::getInstance('ML2T-2');
        $conversion = $converter->findConversion('N*m', 'J');

        $this->assertInstanceOf(Conversion::class, $conversion);
        $this->assertEqualsWithDelta(1.0, $conversion->factor->value, 1e-10);
    }

    #endregion
}
