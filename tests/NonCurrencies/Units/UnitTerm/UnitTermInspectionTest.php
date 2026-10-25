<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\UnitTerm;

use Mossy2100\Quantities\Units\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the inspection methods of UnitTerm.
 */
#[CoversClass(UnitTerm::class)]
final class UnitTermInspectionTest extends TestCase
{
    #region Method isSi() tests.

    /**
     * Test isSi returns true for SI unit.
     */
    public function testIsSiReturnsTrueForSiUnit(): void
    {
        $term = new UnitTerm('m');

        $this->assertTrue($term->isSi());
    }

    /**
     * Test isSi returns true for prefixed SI unit.
     */
    public function testIsSiReturnsTrueForPrefixedSiUnit(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $this->assertTrue($term->isSi());
    }

    /**
     * Test isSi returns false for Imperial unit.
     */
    public function testIsSiReturnsFalseForImperialUnit(): void
    {
        $term = new UnitTerm('ft');

        $this->assertFalse($term->isSi());
    }

    #endregion

    #region Method isBase() tests.

    /**
     * Test isBase returns true for base unit.
     */
    public function testIsBaseReturnsTrueForBaseUnit(): void
    {
        $term = new UnitTerm('m');

        $this->assertTrue($term->isBase());
    }

    /**
     * Test isBase returns true for base unit with exponent.
     */
    public function testIsBaseReturnsTrueForBaseUnitWithExponent(): void
    {
        $term = new UnitTerm('m', null, 2);

        $this->assertTrue($term->isBase());
    }

    /**
     * Test isBase returns false for compound unit.
     */
    public function testIsBaseReturnsFalseForCompoundUnit(): void
    {
        // Newton has multiple dimension terms.
        $term = new UnitTerm('N');

        $this->assertFalse($term->isBase());
    }

    #endregion
}
