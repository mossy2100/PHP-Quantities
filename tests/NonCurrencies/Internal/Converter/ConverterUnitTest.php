<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Internal\Converter;

use Mossy2100\Quantities\Internal\Converter;
use Mossy2100\Quantities\Units\CompoundUnit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the unit methods of Converter.
 */
#[CoversClass(Converter::class)]
class ConverterUnitTest extends TestCase
{
    #region Method hasUnit() tests.

    /**
     * Test hasUnit() returns true for a unit that has been added.
     */
    public function testHasUnitReturnsTrueForAddedUnit(): void
    {
        Converter::removeAllInstances();
        $converter = Converter::getInstance('L');

        $unit = CompoundUnit::parse('m');
        $converter->addUnit($unit);

        $this->assertTrue($converter->hasUnit($unit));
    }

    /**
     * Test hasUnit() returns false for a unit that has not been added.
     */
    public function testHasUnitReturnsFalseForUnknownUnit(): void
    {
        Converter::removeAllInstances();
        $converter = Converter::getInstance('L');
        $converter->removeAllUnits();

        $unit = CompoundUnit::parse('m');

        $this->assertFalse($converter->hasUnit($unit));
    }

    #endregion

    #region Method removeUnit() tests.

    /**
     * Test removeUnit removes a unit from the unit list.
     */
    public function testRemoveUnitRemovesFromList(): void
    {
        Converter::removeAllInstances();
        $converter = Converter::getInstance('L');

        $unit = CompoundUnit::parse('m');
        $converter->addUnit($unit);
        $this->assertArrayHasKey('m', $converter->units);

        $converter->removeUnit($unit);

        $this->assertArrayNotHasKey('m', $converter->units);
    }

    #endregion

    #region Method removeAllUnits() tests.

    /**
     * Test removeAllUnits clears the unit list.
     */
    public function testRemoveAllUnitsClearsList(): void
    {
        Converter::removeAllInstances();
        $converter = Converter::getInstance('L');

        $this->assertNotEmpty($converter->units);

        $converter->removeAllUnits();

        $this->assertEmpty($converter->units);
    }

    #endregion
}
