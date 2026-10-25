<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Units\UnitTerm;

use Mossy2100\Quantities\Units\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the transformation methods of UnitTerm.
 */
#[CoversClass(UnitTerm::class)]
final class UnitTermTransformationTest extends TestCase
{
    #region Method withExponent() tests.

    /**
     * Test withExponent() changes exponent.
     */
    public function testWithExponentChangesExponent(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $newTerm = $term->withExponent(3);

        $this->assertSame(3, $newTerm->exponent);
        $this->assertSame('k', $newTerm->prefix?->asciiSymbol);
    }

    /**
     * Test withExponent() returns new instance.
     */
    public function testWithExponentReturnsNewInstance(): void
    {
        $term = new UnitTerm('m');

        $newTerm = $term->withExponent(2);

        $this->assertNotSame($term, $newTerm);
    }

    #endregion

    #region Method removeExponent() tests.

    /**
     * Test removeExponent() resets to 1.
     */
    public function testRemoveExponentResetsToOne(): void
    {
        $term = new UnitTerm('m', 'k', 3);

        $newTerm = $term->removeExponent();

        $this->assertSame(1, $newTerm->exponent);
        $this->assertSame('k', $newTerm->prefix?->asciiSymbol);
    }

    #endregion

    #region Method removePrefix() tests.

    /**
     * Test removePrefix() removes prefix.
     */
    public function testRemovePrefixRemovesPrefix(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $newTerm = $term->removePrefix();

        $this->assertNull($newTerm->prefix);
        $this->assertSame(2, $newTerm->exponent);
    }

    /**
     * Test removePrefix returns different instance when no prefix exists.
     */
    public function testRemovePrefixReturnsDifferentInstanceWhenNoPrefix(): void
    {
        $term = new UnitTerm('m', null, 2);

        $newTerm = $term->removePrefix();

        $this->assertNotSame($term, $newTerm);
    }

    #endregion
}
