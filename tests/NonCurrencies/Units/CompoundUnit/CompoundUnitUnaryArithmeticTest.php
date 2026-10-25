<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\CompoundUnit;

use Mossy2100\Quantities\Units\CompoundUnit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the unary arithmetic methods of CompoundUnit.
 */
#[CoversClass(CompoundUnit::class)]
class CompoundUnitUnaryArithmeticTest extends TestCase
{
    #region Method recip() tests.

    public function testRecipSingleUnit(): void
    {
        $du = CompoundUnit::parse('m');
        $recip = $du->recip();

        $this->assertSame('m-1', $recip->format(true));
        $this->assertSame('L-1', $recip->dimension);
    }

    public function testRecipUnitWithExponent(): void
    {
        $du = CompoundUnit::parse('m2');
        $recip = $du->recip();

        $this->assertSame('m-2', $recip->format(true));
    }

    public function testRecipCompoundUnit(): void
    {
        $du = CompoundUnit::parse('m/s');
        $recip = $du->recip();

        // m⋅s⁻¹ inverted is m⁻¹⋅s
        $this->assertSame('s/m', $recip->format(true));
    }

    public function testRecipDoesNotModifyOriginal(): void
    {
        $du = CompoundUnit::parse('m');
        $recip = $du->recip();

        $this->assertSame('m', $du->format(true));
        $this->assertSame('m-1', $recip->format(true));
    }

    public function testRecipEmpty(): void
    {
        $du = new CompoundUnit();
        $recip = $du->recip();

        $this->assertSame('', $recip->format(true));
    }

    #endregion
}
