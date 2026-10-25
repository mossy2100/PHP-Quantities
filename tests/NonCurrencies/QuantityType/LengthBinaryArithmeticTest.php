<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\QuantityType;

use Mossy2100\Core\Traits\Asserts\FloatAssertions;
use Mossy2100\Quantities\QuantityType\Length;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the binary arithmetic methods of Length.
 */
#[CoversClass(Length::class)]
final class LengthBinaryArithmeticTest extends TestCase
{
    use FloatAssertions;

    #region Method add() tests.

    /**
     * Test adding SI units (meters + kilometers).
     */
    public function testAddMetersAndKilometers(): void
    {
        $a = new Length(500, 'm');
        $b = new Length(1, 'km');
        $result = $a->add($b);

        $this->assertInstanceOf(Length::class, $result);
        $this->assertSame(1500.0, $result->value);
        $this->assertSame('m', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test adding SI units (kilometers + meters).
     */
    public function testAddKilometersAndMeters(): void
    {
        $a = new Length(1, 'km');
        $b = new Length(500, 'm');
        $result = $a->add($b);

        $this->assertSame(1.5, $result->value);
        $this->assertSame('km', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test adding SI units (centimeters + meters).
     */
    public function testAddCentimetersAndMeters(): void
    {
        $a = new Length(50, 'cm');
        $b = new Length(1, 'm');
        $result = $a->add($b);

        $this->assertSame(150.0, $result->value);
        $this->assertSame('cm', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test adding Imperial units (feet + inches).
     */
    public function testAddFeetAndInches(): void
    {
        $a = new Length(2, 'ft');
        $b = new Length(6, 'in');
        $result = $a->add($b);

        $this->assertSame(2.5, $result->value);
        $this->assertSame('ft', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test adding Imperial units (yards + feet).
     */
    public function testAddYardsAndFeet(): void
    {
        $a = new Length(1, 'yd');
        $b = new Length(3, 'ft');
        $result = $a->add($b);

        $this->assertSame(2.0, $result->value);
        $this->assertSame('yd', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test adding Imperial units (miles + yards).
     */
    public function testAddMilesAndYards(): void
    {
        $a = new Length(1, 'mi');
        $b = new Length(1760, 'yd');
        $result = $a->add($b);

        $this->assertSame(2.0, $result->value);
        $this->assertSame('mi', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test adding mixed units (meters + feet).
     */
    public function testAddMetersAndFeet(): void
    {
        $a = new Length(1, 'm');
        $b = new Length(1, 'ft');
        $result = $a->add($b);

        // 1 m + 0.3048 m = 1.3048 m
        $this->assertSame(1.3048, $result->value);
        $this->assertSame('m', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test adding mixed units (feet + meters).
     */
    public function testAddFeetAndMeters(): void
    {
        $a = new Length(1, 'ft');
        $b = new Length(1, 'm');
        $result = $a->add($b);

        // 1 ft + (1/0.3048) ft ≈ 4.28 ft
        $this->assertApproxEqual(1 + 1 / 0.3048, $result->value);
        $this->assertSame('ft', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test adding mixed units (kilometers + miles).
     */
    public function testAddKilometersAndMiles(): void
    {
        $a = new Length(1, 'km');
        $b = new Length(1, 'mi');
        $result = $a->add($b);

        // 1 km + 1.609344 km = 2.609344 km
        $this->assertSame(2.609344, $result->value);
        $this->assertSame('km', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test adding mixed units (inches + centimeters).
     */
    public function testAddInchesAndCentimeters(): void
    {
        $a = new Length(1, 'in');
        $b = new Length(2.54, 'cm');
        $result = $a->add($b);

        // 1 in + 1 in = 2 in
        $this->assertApproxEqual(2.0, $result->value);
        $this->assertSame('in', $result->compoundUnit->asciiSymbol);
    }

    #endregion

    #region Method mul() tests.

    /**
     * Test multiplying meters by a scalar.
     */
    public function testMulMetersByScalar(): void
    {
        $length = new Length(5, 'm');
        $result = $length->mul(3);

        $this->assertInstanceOf(Length::class, $result);
        $this->assertSame(15.0, $result->value);
        $this->assertSame('m', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test multiplying kilometers by a scalar.
     */
    public function testMulKilometersByScalar(): void
    {
        $length = new Length(2.5, 'km');
        $result = $length->mul(4);

        $this->assertSame(10.0, $result->value);
        $this->assertSame('km', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test multiplying feet by a scalar.
     */
    public function testMulFeetByScalar(): void
    {
        $length = new Length(6, 'ft');
        $result = $length->mul(2);

        $this->assertSame(12.0, $result->value);
        $this->assertSame('ft', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test multiplying by zero.
     */
    public function testMulByZero(): void
    {
        $length = new Length(100, 'm');
        $result = $length->mul(0);

        $this->assertSame(0.0, $result->value);
    }

    /**
     * Test multiplying by a fractional scalar.
     */
    public function testMulByFraction(): void
    {
        $length = new Length(10, 'm');
        $result = $length->mul(0.5);

        $this->assertSame(5.0, $result->value);
    }

    /**
     * Test multiplying by a negative scalar.
     */
    public function testMulByNegative(): void
    {
        $length = new Length(5, 'm');
        $result = $length->mul(-2);

        $this->assertSame(-10.0, $result->value);
    }

    #endregion

    #region Method div() tests.

    /**
     * Test dividing meters by a scalar.
     */
    public function testDivMetersByScalar(): void
    {
        $length = new Length(15, 'm');
        $result = $length->div(3);

        $this->assertInstanceOf(Length::class, $result);
        $this->assertSame(5.0, $result->value);
        $this->assertSame('m', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test dividing kilometers by a scalar.
     */
    public function testDivKilometersByScalar(): void
    {
        $length = new Length(10, 'km');
        $result = $length->div(4);

        $this->assertSame(2.5, $result->value);
        $this->assertSame('km', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test dividing feet by a scalar.
     */
    public function testDivFeetByScalar(): void
    {
        $length = new Length(12, 'ft');
        $result = $length->div(3);

        $this->assertSame(4.0, $result->value);
        $this->assertSame('ft', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test dividing by a fractional scalar.
     */
    public function testDivByFraction(): void
    {
        $length = new Length(10, 'm');
        $result = $length->div(0.5);

        $this->assertSame(20.0, $result->value);
    }

    /**
     * Test dividing by a negative scalar.
     */
    public function testDivByNegative(): void
    {
        $length = new Length(10, 'm');
        $result = $length->div(-2);

        $this->assertSame(-5.0, $result->value);
    }

    /**
     * Test dividing results in non-integer value.
     */
    public function testDivNonInteger(): void
    {
        $length = new Length(10, 'm');
        $result = $length->div(3);

        $this->assertApproxEqual(10.0 / 3.0, $result->value);
    }

    #endregion
}
