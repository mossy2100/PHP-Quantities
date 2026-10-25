<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Internal\Converter;

use Mossy2100\Quantities\Internal\Converter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the properties of Converter.
 */
#[CoversClass(Converter::class)]
class ConverterPropertiesTest extends TestCase
{
    #region Property $quantityType tests.

    /**
     * Test quantityType property returns QuantityType for a registered dimension.
     */
    public function testQuantityTypePropertyReturnsQuantityType(): void
    {
        $converter = Converter::getInstance('L');

        $this->assertNotNull($converter->quantityType);
        $this->assertSame('length', $converter->quantityType->name);
    }

    /**
     * Test quantityType property returns null for an unregistered dimension.
     */
    public function testQuantityTypePropertyReturnsNullForUnregisteredDimension(): void
    {
        $converter = Converter::getInstance('L5T3');

        $this->assertNull($converter->quantityType);
    }

    #endregion
}
