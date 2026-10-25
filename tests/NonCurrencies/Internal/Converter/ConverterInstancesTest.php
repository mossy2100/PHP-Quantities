<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Internal\Converter;

use DomainException;
use Mossy2100\Quantities\Internal\Converter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the instance management methods of Converter.
 */
#[CoversClass(Converter::class)]
class ConverterInstancesTest extends TestCase
{
    #region Method removeAllInstances() tests.

    /**
     * Test removeAllInstances clears cached instances.
     */
    public function testResetClearsCachedInstances(): void
    {
        // Get an instance to populate the cache.
        $converter1 = Converter::getInstance('L');

        // Reset the cache.
        Converter::removeAllInstances();

        // Get a new instance - should be a different object.
        $converter2 = Converter::getInstance('L');

        // They should not be the same instance.
        $this->assertNotSame($converter1, $converter2);
    }

    #endregion

    #region Method getInstances() tests.

    /**
     * Test getInstances returns all cached Converter instances.
     */
    public function testGetInstancesReturnsAllCachedConverters(): void
    {
        Converter::removeAllInstances();

        $this->assertEmpty(Converter::getInstances());

        $length = Converter::getInstance('L');
        $time = Converter::getInstance('T');

        $instances = Converter::getInstances();

        $this->assertCount(2, $instances);
        $this->assertSame($length, $instances['L']);
        $this->assertSame($time, $instances['T']);
    }

    #endregion

    #region Method removeInstance() tests.

    /**
     * Test removeInstance removes a single cached Converter.
     */
    public function testRemoveInstanceRemovesCachedConverter(): void
    {
        Converter::removeAllInstances();

        Converter::getInstance('L');
        Converter::getInstance('T');

        $this->assertCount(2, Converter::getInstances());

        Converter::removeInstance('L');

        $instances = Converter::getInstances();
        $this->assertCount(1, $instances);
        $this->assertArrayHasKey('T', $instances);
        $this->assertArrayNotHasKey('L', $instances);
    }

    #endregion

    #region Method getInstance() tests.

    /**
     * Test getInstance returns instance for valid dimension.
     */
    public function testGetInstanceReturnsInstance(): void
    {
        $converter = Converter::getInstance('L');

        $this->assertInstanceOf(Converter::class, $converter);
        $this->assertSame('L', $converter->dimension);
    }

    /**
     * Test getInstance throws for invalid dimension.
     */
    public function testGetInstanceThrowsForInvalidDimension(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code: 'X'.");

        Converter::getInstance('X');
    }

    /**
     * Test getInstance returns same instance for same dimension (singleton).
     */
    public function testGetInstanceReturnsSameInstance(): void
    {
        $converter1 = Converter::getInstance('L');
        $converter2 = Converter::getInstance('L');

        $this->assertSame($converter1, $converter2);
    }

    /**
     * Test getInstance returns different instances for different dimensions.
     */
    public function testGetInstanceReturnsDifferentInstancesForDifferentDimensions(): void
    {
        $lengthConverter = Converter::getInstance('L');
        $timeConverter = Converter::getInstance('T');

        $this->assertNotSame($lengthConverter, $timeConverter);
        $this->assertSame('L', $lengthConverter->dimension);
        $this->assertSame('T', $timeConverter->dimension);
    }

    #endregion
}
