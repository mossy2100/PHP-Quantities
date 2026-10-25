<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\CompoundUnit;

use Mossy2100\Quantities\Quantity;
use Mossy2100\Quantities\Units\CompoundUnit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the helper methods of CompoundUnit.
 */
#[CoversClass(CompoundUnit::class)]
class CompoundUnitHelperTest extends TestCase
{
    #region Method tryExpand() tests.

    // Most of tryExpand()'s observable behaviour is covered by the toSiBase() tests above
    // (e.g. N → kg*m/s2, kN → kg*m/s2, N*s → kg*m/s) and by the multiplier property tests
    // for the prefix magnitudes. The tests below cover the *internal* signals that are not
    // directly observable through the public API: the null-return contract for base and
    // unexpandable inputs, and the caching behaviour.

    /**
     * Test tryExpand returns null for a base unit (internal signal — not observable via toSi).
     */
    public function testTryExpandReturnsNullForBaseUnit(): void
    {
        $du = CompoundUnit::parse('m');

        $this->assertNull($du->tryExpand());
    }

    /**
     * Test tryExpand returns null when a compound unit contains only base terms (internal signal).
     */
    public function testTryExpandReturnsNullForUnexpandableTerm(): void
    {
        // A base unit compound like kg*m is already base — nothing to expand.
        $du = CompoundUnit::parse('kg*m');

        $this->assertNull($du->tryExpand());
    }

    /**
     * Test tryExpand passes base unit terms through unchanged when mixed with non-base terms.
     *
     * N*m: N is non-base (expands to kg*m/s²), m is base (added directly to the result).
     * The combined expansion should have dimension ML²T⁻² (equivalent to a Joule).
     */
    public function testTryExpandWithMixedBaseAndNonBaseTerms(): void
    {
        $du = CompoundUnit::parse('N*m');
        $expansion = $du->tryExpand();

        $this->assertInstanceOf(Quantity::class, $expansion);
        $this->assertSame('ML2T-2', $expansion->compoundUnit->dimension);
    }

    /**
     * Test tryExpand returns the cached expansion on a second call. Internal optimisation,
     * verified by identity (===) on the returned Quantity.
     */
    public function testTryExpandReturnsCachedExpansion(): void
    {
        $du = CompoundUnit::parse('N');

        $expansion1 = $du->tryExpand();
        $expansion2 = $du->tryExpand();

        $this->assertInstanceOf(Quantity::class, $expansion1);
        $this->assertSame($expansion1, $expansion2);
    }

    #endregion
}
