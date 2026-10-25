<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Quantity;

use DomainException;
use Mossy2100\Core\Enums\ExponentFormat;
use Mossy2100\Core\Enums\FloatFormat;
use Mossy2100\Core\Exceptions\FormatException;
use Mossy2100\Quantities\Exceptions\DimensionMismatchException;
use Mossy2100\Quantities\Quantity;
use Mossy2100\Quantities\QuantityType\Angle;
use Mossy2100\Quantities\QuantityType\Length;
use Mossy2100\Quantities\QuantityType\Mass;
use Mossy2100\Quantities\QuantityType\Temperature;
use Mossy2100\Quantities\QuantityType\Time;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RoundingMode;

/**
 * Tests for parsing and formatting Quantity objects.
 */
#[CoversClass(Quantity::class)]
final class QuantityStringsTest extends TestCase
{
    #region Parse tests - basic

    /**
     * Test parsing a simple length with space between value and unit.
     */
    public function testParseBasicLengthWithSpace(): void
    {
        $length = Length::parse('123.45 km');

        $this->assertInstanceOf(Length::class, $length);
        $this->assertSame(123.45, $length->value);
        $this->assertSame('km', $length->compoundUnit->asciiSymbol);
    }

    /**
     * Test parsing a length without space between value and unit.
     */
    public function testParseLengthWithoutSpace(): void
    {
        $length = Length::parse('100m');

        $this->assertInstanceOf(Length::class, $length);
        $this->assertSame(100.0, $length->value);
        $this->assertSame('m', $length->compoundUnit->asciiSymbol);
    }

    /**
     * Test parsing an angle with degree symbol.
     */
    public function testParseAngleWithDegreeSymbol(): void
    {
        $angle = Angle::parse('90deg');

        $this->assertInstanceOf(Angle::class, $angle);
        $this->assertSame(90.0, $angle->value);
        $this->assertSame('deg', $angle->compoundUnit->asciiSymbol);
    }

    /**
     * Test parsing mass in kilograms.
     */
    public function testParseMassKilograms(): void
    {
        $mass = Mass::parse('75 kg');

        $this->assertInstanceOf(Mass::class, $mass);
        $this->assertSame(75.0, $mass->value);
        $this->assertSame('kg', $mass->compoundUnit->asciiSymbol);
    }

    /**
     * Test parsing time with prefixed unit.
     */
    public function testParseTimeWithPrefix(): void
    {
        $time = Time::parse('500 ms');

        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame(500.0, $time->value);
        $this->assertSame('ms', $time->compoundUnit->asciiSymbol);
    }

    #endregion

    #region Parse tests - scientific notation

    /**
     * Test parsing with scientific notation (lowercase e).
     */
    public function testParseScientificNotationLowercase(): void
    {
        $length = Length::parse('1.5e3 m');

        $this->assertSame(1500.0, $length->value);
    }

    /**
     * Test parsing with scientific notation (uppercase E).
     */
    public function testParseScientificNotationUppercase(): void
    {
        $length = Length::parse('2.5E-3 km');

        $this->assertSame(0.0025, $length->value);
    }

    #endregion

    #region Parse tests - negative values

    /**
     * Test parsing negative values.
     */
    public function testParseNegativeValue(): void
    {
        $temp = Temperature::parse('-40 degC');

        $this->assertSame(-40.0, $temp->value);
        $this->assertSame('degC', $temp->compoundUnit->asciiSymbol);
    }

    #endregion

    #region Parse tests - generic Quantity::parse()

    /**
     * Test that Quantity::parse() returns the correct subclass.
     */
    public function testGenericParseReturnsCorrectSubclass(): void
    {
        $length = Quantity::parse('10 m');
        $mass = Quantity::parse('5 kg');
        $time = Quantity::parse('30 s');

        $this->assertInstanceOf(Length::class, $length);
        $this->assertInstanceOf(Mass::class, $mass);
        $this->assertInstanceOf(Time::class, $time);
    }

    #endregion

    #region Parse tests - error handling

    /**
     * Test parsing empty string throws exception.
     */
    public function testParseEmptyStringThrowsException(): void
    {
        $this->expectException(FormatException::class);

        Length::parse('');
    }

    /**
     * Test parsing whitespace-only string throws exception.
     */
    public function testParseWhitespaceOnlyThrowsException(): void
    {
        $this->expectException(FormatException::class);

        Length::parse('   ');
    }

    /**
     * Test parsing invalid format throws exception.
     */
    public function testParseInvalidFormatThrowsException(): void
    {
        $this->expectException(FormatException::class);

        Length::parse('not a valid quantity');
    }

    #endregion

    #region Parse tests - dimension mismatch

    /**
     * Test that parsing a unit with the wrong dimension throws DimensionMismatchException.
     */
    public function testParseWrongDimensionThrowsException(): void
    {
        $this->expectException(DimensionMismatchException::class);

        Length::parse('123 kg');
    }

    /**
     * Test that parsing a compound unit with the wrong dimension throws DimensionMismatchException.
     */
    public function testParseCompoundUnitWrongDimensionThrowsException(): void
    {
        $this->expectException(DimensionMismatchException::class);

        Length::parse('10 kg*m/s2');
    }

    /**
     * Test that Quantity::parse() accepts any valid unit without dimension restriction.
     */
    public function testQuantityParseAcceptsAnyDimension(): void
    {
        $mass = Quantity::parse('123 kg');
        $length = Quantity::parse('456 m');

        $this->assertInstanceOf(Mass::class, $mass);
        $this->assertInstanceOf(Length::class, $length);
    }

    /**
     * Test that subclass parse() works when the dimension matches.
     */
    public function testParseCorrectDimensionSucceeds(): void
    {
        $length = Length::parse('100 km');
        $mass = Mass::parse('50 kg');

        $this->assertInstanceOf(Length::class, $length);
        $this->assertSame(100.0, $length->value);

        $this->assertInstanceOf(Mass::class, $mass);
        $this->assertSame(50.0, $mass->value);
    }

    #endregion

    #region Format tests - basic

    /**
     * Test default formatting (Unicode, fixed point, trim zeros).
     */
    public function testFormatDefault(): void
    {
        $length = new Length(5.0, 'm');

        $this->assertSame('5 m', $length->format());
    }

    /**
     * Test formatting with decimal places.
     */
    public function testFormatWithDecimalPlaces(): void
    {
        $length = new Length(1.5, 'm');

        $this->assertSame('1.5 m', $length->format());
    }

    /**
     * Test formatting large values.
     */
    public function testFormatLargeValue(): void
    {
        $length = new Length(12345678.9, 'm');

        // Scientific notation, 6 significant digits by default.
        $this->assertSame('1.23457×10⁷ m', $length->format(format: FloatFormat::Scientific));

        // Fixed-point notation with 1 decimal place.
        $this->assertSame('12345678.9 m', $length->format(1, format: FloatFormat::FixedPoint));

        // Auto (the default) keeps the full value here, since it holds more information than the scientific form.
        $this->assertSame('12345678.9 m', $length->format());
    }

    #endregion

    #region Format tests - precision control

    /**
     * Test formatting with fixed precision.
     */
    public function testFormatFixedPrecision(): void
    {
        $length = new Length(5.0, 'm');

        $this->assertSame('5.00 m', $length->format(2, false, FloatFormat::FixedPoint));
    }

    /**
     * Test formatting with zero precision.
     */
    public function testFormatZeroPrecision(): void
    {
        $length = new Length(5.7, 'm');

        $this->assertSame('6 m', $length->format(0, format: FloatFormat::FixedPoint));
    }

    /**
     * Test default formatting trims trailing zeros.
     */
    public function testFormatTrimsZerosByDefault(): void
    {
        $length = new Length(5.0, 'm');

        $this->assertSame('5 m', $length->format());
    }

    /**
     * Test format() trims zeros by default even with an explicit precision.
     */
    public function testFormatTrimZerosWithExplicitPrecision(): void
    {
        $length = new Length(5.0, 'm');

        $this->assertSame('5 m', $length->format(2, format: FloatFormat::FixedPoint));
    }

    /**
     * Test format() with trimZeros false preserves zeros at the default precision.
     */
    public function testFormatTrimZerosFalsePreservesZeros(): void
    {
        $length = new Length(5.0, 'm');

        $this->assertSame('5.000000 m', $length->format(trimZeros: false, format: FloatFormat::FixedPoint));
    }

    /**
     * Test format() with Scientific format and explicit precision gives that many significant digits.
     */
    public function testFormatSignificantDigits(): void
    {
        $length = new Length(1234.56, 'm');

        $this->assertSame('1.235×10³ m', $length->format(4, format: FloatFormat::Scientific));
    }

    /**
     * Test format() rounds half away from zero by default, and honors an explicit rounding mode.
     */
    public function testFormatRoundingMode(): void
    {
        $length = new Length(2.5, 'm');

        $this->assertSame('3 m', $length->format(0, format: FloatFormat::FixedPoint));
        $this->assertSame(
            '2 m',
            $length->format(0, format: FloatFormat::FixedPoint, roundingMode: RoundingMode::HalfEven)
        );
    }

    /**
     * Test that trimming does not strip significant zeros from integer values.
     */
    public function testFormatTrimDoesNotStripIntegerZeros(): void
    {
        // Trimming must not strip the trailing zeros of an integer value.
        $length = new Length(1500.0, 'm');

        $this->assertSame('1500 m', $length->format());
    }

    #endregion

    #region Format tests - scientific notation

    /**
     * Test ASCII scientific notation uses lower-case e notation by default.
     */
    public function testFormatScientificNotation(): void
    {
        $length = new Length(1500.0, 'm');
        $result = $length->format(3, false, FloatFormat::Scientific, ascii: true);

        $this->assertSame('1.50e+3 m', $result);
    }

    /**
     * Test formatting with uppercase scientific notation.
     */
    public function testFormatScientificNotationUppercase(): void
    {
        $length = new Length(1500.0, 'm');
        $result = $length->format(3, false, FloatFormat::Scientific, ExponentFormat::AsciiUpperCaseE, ascii: true);

        $this->assertSame('1.50E+3 m', $result);
    }

    /**
     * Test scientific notation uses ×10 with superscript exponent by default (ascii=false).
     */
    public function testFormatScientificNotationUnicode(): void
    {
        $length = new Length(1500.0, 'm');
        $result = $length->format(3, false, FloatFormat::Scientific);

        $this->assertSame('1.50×10³ m', $result);
    }

    /**
     * Test an explicit exponent format overrides the one chosen by $ascii.
     */
    public function testFormatExplicitExponentFormatOverridesAscii(): void
    {
        $length = new Length(1500.0, 'um');
        $result = $length->format(3, false, FloatFormat::Scientific, ExponentFormat::UnicodeMath, ascii: true);

        $this->assertSame('1.50×10³ um', $result);
    }

    /**
     * Test scientific notation with negative exponent uses ×10 with superscript by default.
     */
    public function testFormatScientificNotationUnicodeNegativeExponent(): void
    {
        $length = new Length(0.0025, 'm');
        $result = $length->format(3, false, FloatFormat::Scientific);

        $this->assertSame('2.50×10⁻³ m', $result);
    }

    /**
     * Test scientific notation trims zeros by default and uses ×10.
     */
    public function testFormatScientificNotationUnicodeTrimZeros(): void
    {
        $length = new Length(3000.0, 'm');
        $result = $length->format(format: FloatFormat::Scientific);

        $this->assertSame('3×10³ m', $result);
    }

    /**
     * Test scientific notation with trimZeros false preserves trailing zeros.
     */
    public function testFormatScientificNotationPreservesZeros(): void
    {
        $length = new Length(3000.0, 'm');
        $result = $length->format(5, false, FloatFormat::Scientific);

        $this->assertSame('3.0000×10³ m', $result);
    }

    #endregion

    #region Format tests - ASCII vs Unicode

    /**
     * Test ASCII formatting.
     */
    public function testFormatAscii(): void
    {
        $temp = new Temperature(25, 'degC');

        $this->assertSame('25 degC', $temp->format(ascii: true));
    }

    /**
     * Test Unicode formatting.
     */
    public function testFormatUnicode(): void
    {
        $temp = new Temperature(25, 'degC');

        $this->assertSame('25 °C', $temp->format());
    }

    /**
     * Test formatting angle with degree symbol.
     */
    public function testFormatAngleDegreeSymbol(): void
    {
        $angle = new Angle(45, 'deg');

        // Unicode format - no space before degree symbol
        $this->assertSame('45°', $angle->format());
    }

    /**
     * Test that format()'s auto-spacing drops the space before single-character non-letter symbols
     * like ° and keeps it for letter-based or multi-character symbols.
     */
    public function testFormatAutoSpacingForSpecialUnitSymbols(): void
    {
        // Single non-letter → no space.
        $this->assertSame('45°', new Angle(45, 'deg')->format());

        // Letter-based unit → space.
        $this->assertSame('5 m', new Length(5, 'm')->format());

        // Unit starting with a non-letter but containing letters → space (e.g. °C).
        $this->assertSame('25 °C', new Temperature(25, 'degC')->format());
    }

    #endregion

    #region Format tests - space control

    /**
     * Test formatting with explicit space.
     */
    public function testFormatWithExplicitSpace(): void
    {
        $angle = new Angle(45, 'deg');

        $this->assertSame('45 °', $angle->format(includeSpace: true));
    }

    /**
     * Test formatting without space.
     */
    public function testFormatWithoutSpace(): void
    {
        $length = new Length(100, 'm');

        $this->assertSame('100m', $length->format(includeSpace: false));
    }

    #endregion

    #region __toString tests

    /**
     * Test __toString uses default format.
     */
    public function testToString(): void
    {
        $length = new Length(5.5, 'm');

        $this->assertSame('5.5 m', (string) $length);
    }

    /**
     * Test __toString with prefixed unit.
     */
    public function testToStringWithPrefix(): void
    {
        $length = new Length(2.5, 'km');

        $this->assertSame('2.5 km', (string) $length);
    }

    /**
     * Test __toString with negative value.
     */
    public function testToStringNegative(): void
    {
        $temp = new Temperature(-10, 'degC');

        $this->assertSame('-10 °C', (string) $temp);
    }

    /**
     * Test __toString with zero value.
     */
    public function testToStringZero(): void
    {
        $length = new Length(0, 'm');

        $this->assertSame('0 m', (string) $length);
    }

    #endregion

    #region Round-trip tests

    /**
     * Test parse-format round trip.
     */
    public function testRoundTripParseFormat(): void
    {
        $original = '123.45 km';
        $parsed = Length::parse($original);
        $formatted = $parsed->format(2, false, FloatFormat::FixedPoint, includeSpace: true);

        $this->assertSame($original, $formatted);
    }

    #endregion

    #region Format error handling tests

    /**
     * Test format() with negative precision throws exception.
     */
    public function testFormatNegativePrecisionThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid number of decimal places: -1.');

        $length = new Length(10, 'm');
        $length->format(-1, format: FloatFormat::FixedPoint);
    }

    /**
     * Test format() with precision > 17 throws exception.
     */
    public function testFormatPrecisionTooHighThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid number of decimal places: 18.');

        $length = new Length(10, 'm');
        $length->format(18, format: FloatFormat::FixedPoint);
    }

    /**
     * Test format() with zero significant digits throws exception.
     */
    public function testFormatZeroSignificantDigitsThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid number of significant figures: 0.');

        $length = new Length(10, 'm');
        $length->format(0, format: FloatFormat::Scientific);
    }

    #endregion

    #region Dimensionless format tests

    /**
     * Test formatting a dimensionless quantity.
     */
    public function testFormatDimensionless(): void
    {
        $qty = Quantity::create(42.5, '');

        $this->assertSame('42.5', $qty->format());
    }

    /**
     * Test __toString on dimensionless quantity.
     */
    public function testToStringDimensionless(): void
    {
        $qty = Quantity::create(3.14159, '');

        $this->assertSame('3.14159', (string) $qty);
    }

    #endregion
}
