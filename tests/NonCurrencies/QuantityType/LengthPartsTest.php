<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\QuantityType;

use Mossy2100\Quantities\QuantityType\Length;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the parts methods of Length.
 */
#[CoversClass(Length::class)]
final class LengthPartsTest extends TestCase
{
    #region Method fromParts() tests.

    /**
     * Test fromParts with feet and inches.
     */
    public function testFromPartsFeetInches(): void
    {
        $length = Length::fromParts(['ft' => 5, 'in' => 6]);

        // 5 feet + 6 inches = 5.5 feet
        $this->assertSame(5.5, $length->value);
        $this->assertSame('ft', $length->compoundUnit->asciiSymbol);
    }

    /**
     * Test fromParts with miles and yards.
     */
    public function testFromPartsMilesYards(): void
    {
        $length = Length::fromParts(['mi' => 1, 'yd' => 880]);

        // 1 mile + 880 yards = 1.5 miles
        $this->assertSame(7920.0, $length->value);  // in feet (default)
        $this->assertSame('ft', $length->compoundUnit->asciiSymbol);
    }

    /**
     * Test fromParts with negative sign.
     */
    public function testFromPartsNegative(): void
    {
        $length = Length::fromParts(['ft' => 6, 'sign' => -1]);

        $this->assertSame(-6.0, $length->value);
    }

    /**
     * Test fromParts with negative value uses sign key instead.
     */
    public function testFromPartsNegativeValueUsesSign(): void
    {
        // Negative values in parts are handled via the 'sign' key
        $length = Length::fromParts(['ft' => 5, 'sign' => -1]);

        $this->assertSame(-5.0, $length->value);
    }

    /**
     * Test fromParts uses default result unit from config.
     */
    public function testFromPartsDefaultResultUnit(): void
    {
        $length = Length::fromParts(['yd' => 1]);

        // Default result unit for Length is 'ft'
        $this->assertSame('ft', $length->compoundUnit->asciiSymbol);
    }

    #endregion

    #region Method toParts() tests.

    /**
     * Test toParts decomposes length correctly.
     */
    public function testToParts(): void
    {
        // 5.5 feet = 1 yard + 2 feet + 6 inches (since 3 ft = 1 yd)
        $length = new Length(5.5, 'ft');
        $parts = $length->toParts(precision: 0);

        $this->assertSame(1, $parts['sign']);
        $this->assertSame(0, $parts['mi']);
        $this->assertSame(1, $parts['yd']);
        $this->assertSame(2, $parts['ft']);
        $this->assertSame(6.0, $parts['in']);
    }

    /**
     * Test toParts with negative value.
     */
    public function testToPartsNegative(): void
    {
        // -5.5 feet = -(1 yard + 2 feet + 6 inches)
        $length = new Length(-5.5, 'ft');
        $parts = $length->toParts(precision: 0);

        $this->assertSame(-1, $parts['sign']);
        $this->assertSame(1, $parts['yd']);
        $this->assertSame(2, $parts['ft']);
        $this->assertSame(6.0, $parts['in']);
    }

    /**
     * Test toParts with precision causes carry.
     */
    public function testToPartsCarry(): void
    {
        $length = new Length(2.99999999, 'ft');  // Just under 3 feet = 1 yard
        $parts = $length->toParts(precision: 0, partUnitSymbols: ['yd', 'ft']);

        // Should round to 3 feet and carry to 1 yard.
        $this->assertSame(1, $parts['yd']);
        $this->assertSame(0.0, $parts['ft']);
    }

    #endregion

    #region Method formatParts() tests.

    /**
     * Test formatParts with feet and inches.
     */
    public function testFormatPartsFeetInches(): void
    {
        // 5.5 feet = 1 yard 2 feet 6 inches
        $length = new Length(5.5, 'ft');
        $result = $length->formatParts();

        $this->assertSame('1yd 2ft 6in', $result);
    }

    /**
     * Test formatParts with yards, feet, and inches.
     */
    public function testFormatPartsYardsFeetInches(): void
    {
        // 4.5 feet = 1 yard 1 foot 6 inches
        $length = new Length(4.5, 'ft');
        $result = $length->formatParts();

        $this->assertSame('1yd 1ft 6in', $result);
    }

    /**
     * Test formatParts with precision.
     */
    public function testFormatPartsWithPrecision(): void
    {
        // 5.541666... feet = 1 yard 2 feet 6.5 inches
        $length = new Length(5.541666666666667, 'ft');
        $result = $length->formatParts(precision: 1);

        $this->assertSame('1yd 2ft 6.5in', $result);
    }

    /**
     * Test formatParts for negative length.
     */
    public function testFormatPartsNegative(): void
    {
        // -5.5 feet = -(1 yard 2 feet 6 inches)
        $length = new Length(-5.5, 'ft');
        $result = $length->formatParts();

        $this->assertSame('-1yd 2ft 6in', $result);
    }

    /**
     * Test formatParts showZeros option.
     */
    public function testFormatPartsShowZeros(): void
    {
        $length = new Length(5280, 'ft');  // 1 mile
        $result = $length->formatParts(showZeros: true);

        // Shows all parts including zeros (default: mi, yd, ft, in).
        $this->assertSame('1mi 0yd 0ft 0in', $result);
    }

    #endregion

    #region Round-trip tests.

    /**
     * Test parts round-trip conversion.
     */
    public function testPartsRoundTrip(): void
    {
        $length = Length::fromParts(['yd' => 1, 'ft' => 2, 'in' => 6]);
        $formatted = $length->formatParts();

        $this->assertSame('1yd 2ft 6in', $formatted);
    }

    #endregion
}
