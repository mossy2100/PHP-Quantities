<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\UnitTerm;

use Mossy2100\Quantities\Units\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for the comparison methods of UnitTerm.
 */
#[CoversClass(UnitTerm::class)]
final class UnitTermComparisonTest extends TestCase
{
    #region Method equal() tests.

    /**
     * Test equal returns true for same instance.
     */
    public function testEqualReturnsTrueForSameInstance(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $this->assertTrue($term->equal($term));
    }

    /**
     * Test equal returns true for equivalent terms.
     */
    public function testEqualReturnsTrueForEquivalentTerms(): void
    {
        $term1 = new UnitTerm('m', 'k', 2);
        $term2 = new UnitTerm('m', 'k', 2);

        $this->assertTrue($term1->equal($term2));
    }

    /**
     * Test equal returns false for different base units.
     */
    public function testEqualReturnsFalseForDifferentUnits(): void
    {
        $term1 = new UnitTerm('m');
        $term2 = new UnitTerm('s');

        $this->assertFalse($term1->equal($term2));
    }

    /**
     * Test equal returns false for different prefixes.
     */
    public function testEqualReturnsFalseForDifferentPrefixes(): void
    {
        $term1 = new UnitTerm('m', 'k');
        $term2 = new UnitTerm('m', 'M');

        $this->assertFalse($term1->equal($term2));
    }

    /**
     * Test equal returns false for different exponents.
     */
    public function testEqualReturnsFalseForDifferentExponents(): void
    {
        $term1 = new UnitTerm('m', null, 2);
        $term2 = new UnitTerm('m', null, 3);

        $this->assertFalse($term1->equal($term2));
    }

    /**
     * Test equal returns false for different types.
     */
    public function testEqualReturnsFalseForDifferentTypes(): void
    {
        $term = new UnitTerm('m');

        $this->assertFalse($term->equal('m'));
        $this->assertFalse($term->equal(1));
        $this->assertFalse($term->equal(null));
        $this->assertFalse($term->equal(new stdClass()));
    }

    #endregion
}
