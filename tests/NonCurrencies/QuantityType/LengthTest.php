<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\QuantityType;

use Mossy2100\Core\Traits\Asserts\FloatAssertions;
use Mossy2100\Quantities\QuantityType\Length;
use Mossy2100\Quantities\Tests\NonCurrencies\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the unit definitions and conversions of Length.
 */
#[CoversClass(Length::class)]
final class LengthTest extends TestCase
{
    use ArrayShapeTrait;
    use FloatAssertions;

    #region Method getUnitDefinitions() tests.

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = Length::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    #endregion

    #region Method getConversionDefinitions() tests.

    /**
     * Test getConversionDefinitions() returns valid conversion definitions.
     */
    public function testGetConversionDefinitionsReturnsValidArray(): void
    {
        $conversions = Length::getConversionDefinitions();

        $this->assertValidConversionDefinitionsShape($conversions);
    }

    #endregion

    #region Metric conversion tests.

    /**
     * Test converting meters to kilometers.
     */
    public function testConvertMetersToKilometers(): void
    {
        $length = new Length(1000, 'm');
        $km = $length->to('km');

        $this->assertInstanceOf(Length::class, $km);
        $this->assertSame(1.0, $km->value);
        $this->assertSame('km', $km->compoundUnit->asciiSymbol);
    }

    /**
     * Test converting kilometers to meters.
     */
    public function testConvertKilometersToMeters(): void
    {
        $length = new Length(1, 'km');
        $m = $length->to('m');

        $this->assertSame(1000.0, $m->value);
    }

    /**
     * Test converting meters to centimeters.
     */
    public function testConvertMetersToCentimeters(): void
    {
        $length = new Length(1, 'm');
        $cm = $length->to('cm');

        $this->assertSame(100.0, $cm->value);
    }

    /**
     * Test converting centimeters to millimeters.
     */
    public function testConvertCentimetersToMillimeters(): void
    {
        $length = new Length(1, 'cm');
        $mm = $length->to('mm');

        $this->assertSame(10.0, $mm->value);
    }

    /**
     * Test converting meters to micrometers.
     */
    public function testConvertMetersToMicrometers(): void
    {
        $length = new Length(1, 'm');
        $um = $length->to('μm');

        $this->assertSame(1000000.0, $um->value);
    }

    /**
     * Test converting meters to nanometers.
     */
    public function testConvertMetersToNanometers(): void
    {
        $length = new Length(1, 'm');
        $nm = $length->to('nm');

        $this->assertApproxEqual(1000000000.0, $nm->value);
    }

    #endregion

    #region Imperial/US conversion tests.

    /**
     * Test converting feet to inches.
     */
    public function testConvertFeetToInches(): void
    {
        $length = new Length(1, 'ft');
        $in = $length->to('in');

        $this->assertSame(12.0, $in->value);
    }

    /**
     * Test converting yards to feet.
     */
    public function testConvertYardsToFeet(): void
    {
        $length = new Length(1, 'yd');
        $ft = $length->to('ft');

        $this->assertSame(3.0, $ft->value);
    }

    /**
     * Test converting miles to yards.
     */
    public function testConvertMilesToYards(): void
    {
        $length = new Length(1, 'mi');
        $yd = $length->to('yd');

        $this->assertSame(1760.0, $yd->value);
    }

    #endregion

    #region Cross-system conversion tests.

    /**
     * Test converting meters to feet.
     */
    public function testConvertMetersToFeet(): void
    {
        $length = new Length(1, 'm');
        $ft = $length->to('ft');

        // 1 meter = 1/0.3048 feet
        $this->assertApproxEqual(1 / 0.3048, $ft->value);
    }

    /**
     * Test converting feet to meters.
     */
    public function testConvertFeetToMeters(): void
    {
        $length = new Length(1, 'ft');
        $m = $length->to('m');

        // 1 foot = 0.3048 meters (exactly)
        $this->assertSame(0.3048, $m->value);
    }

    /**
     * Test converting inches to centimeters.
     */
    public function testConvertInchesToCentimeters(): void
    {
        $length = new Length(1, 'in');
        $cm = $length->to('cm');

        // 1 inch = 2.54 cm (exactly)
        $this->assertSame(2.54, $cm->value);
    }

    /**
     * Test converting centimeters to inches.
     */
    public function testConvertCentimetersToInches(): void
    {
        $length = new Length(2.54, 'cm');
        $in = $length->to('in');

        $this->assertApproxEqual(1.0, $in->value);
    }

    /**
     * Test converting miles to kilometers.
     */
    public function testConvertMilesToKilometers(): void
    {
        $length = new Length(1, 'mi');
        $km = $length->to('km');

        // 1 mile = 1.609344 km (exactly)
        $this->assertSame(1.609344, $km->value);
    }

    /**
     * Test converting kilometers to miles.
     */
    public function testConvertKilometersToMiles(): void
    {
        $length = new Length(1.609344, 'km');
        $mi = $length->to('mi');

        $this->assertApproxEqual(1.0, $mi->value);
    }

    /**
     * Test converting yards to meters.
     */
    public function testConvertYardsToMeters(): void
    {
        $length = new Length(1, 'yd');
        $m = $length->to('m');

        // 1 yard = 0.9144 meters (exactly)
        $this->assertSame(0.9144, $m->value);
    }

    #endregion

    #region Scientific unit tests.

    /**
     * Test converting astronomical units to meters.
     */
    public function testConvertAuToMeters(): void
    {
        $length = new Length(1, 'au');
        $m = $length->to('m');

        $this->assertSame(149597870700.0, $m->value);
    }

    /**
     * Test converting light years to meters.
     */
    public function testConvertLightYearsToMeters(): void
    {
        $length = new Length(1, 'ly');
        $m = $length->to('m');

        $this->assertSame(9460730472580800.0, $m->value);
    }

    /**
     * Test converting parsecs to astronomical units.
     */
    public function testConvertParsecsToAu(): void
    {
        $length = new Length(1, 'pc');
        $au = $length->to('au');

        // 1 parsec = 648000/π AU
        $this->assertApproxEqual(648000 / M_PI, $au->value);
    }

    /**
     * Test converting kiloparsecs to parsecs.
     */
    public function testConvertKiloparsecsToParsecs(): void
    {
        $length = new Length(1, 'kpc');
        $pc = $length->to('pc');

        $this->assertSame(1000.0, $pc->value);
    }

    /**
     * Test converting megaparsecs to parsecs.
     */
    public function testConvertMegaparsecsToParsecs(): void
    {
        $length = new Length(1, 'Mpc');
        $pc = $length->to('pc');

        $this->assertSame(1000000.0, $pc->value);
    }

    /**
     * Test converting parsecs to kiloparsecs.
     */
    public function testConvertParsecsToKiloparsecs(): void
    {
        $length = new Length(1000, 'pc');
        $kpc = $length->to('kpc');

        $this->assertSame(1.0, $kpc->value);
    }

    /**
     * Test converting AU to kiloparsecs.
     */
    public function testConvertAstronomicalUnitsToKiloparsecs(): void
    {
        $length = new Length(1e9, 'au');
        $kpc = $length->to('kpc');

        $this->assertApproxEqual(4.84813678202, $kpc->value, 1e-8);
    }

    /**
     * Test converting light years to megaparsecs.
     */
    public function testConvertLightYearsToMegaparsecs(): void
    {
        $length = new Length(1e8, 'ly');
        $mpc = $length->to('Mpc');

        $this->assertApproxEqual(30.660139393, $mpc->value, 1e-8);
    }

    /**
     * Test converting kiloparsecs to light years.
     */
    public function testConvertKiloparsecsToLightYears(): void
    {
        $length = new Length(42, 'kpc');
        $ly = $length->to('ly');

        $this->assertApproxEqual(136986, $ly->value, 1e-5);
    }

    #endregion

    #region CSS unit tests.

    /**
     * Test converting inches to pixels.
     */
    public function testConvertInchesToPixels(): void
    {
        $length = new Length(1, 'in');
        $px = $length->to('px');

        $this->assertSame(96.0, $px->value);
    }

    /**
     * Test converting inches to points.
     */
    public function testConvertInchesToPoints(): void
    {
        $length = new Length(1, 'in');
        $pt = $length->to('pt');

        $this->assertSame(72.0, $pt->value);
    }

    /**
     * Test converting inches to picas.
     */
    public function testConvertInchesToPicas(): void
    {
        $length = new Length(1, 'in');
        $pica = $length->to('p');

        $this->assertSame(6.0, $pica->value);
    }

    /**
     * Test converting points to picas.
     */
    public function testConvertPointsToPicas(): void
    {
        $length = new Length(12, 'pt');
        $pica = $length->to('p');

        $this->assertSame(1.0, $pica->value);
    }

    #endregion

    #region Nautical unit tests.

    /**
     * Test converting nautical miles to meters.
     */
    public function testConvertNauticalMilesToMeters(): void
    {
        $length = new Length(1, 'nmi');
        $m = $length->to('m');

        $this->assertSame(1852.0, $m->value);
    }

    /**
     * Test converting fathoms to yards.
     */
    public function testConvertFathomsToYards(): void
    {
        $length = new Length(1, 'ftm');
        $yd = $length->to('yd');

        $this->assertSame(2.0, $yd->value);
    }

    /**
     * Test converting fathoms to feet.
     */
    public function testConvertFathomsToFeet(): void
    {
        $length = new Length(1, 'ftm');
        $ft = $length->to('ft');

        $this->assertSame(6.0, $ft->value);
    }

    #endregion

    #region Method convert() tests.

    /**
     * Test static convert method.
     */
    public function testStaticConvertMethod(): void
    {
        $value = Length::convert(1000, 'm', 'km');

        $this->assertSame(1.0, $value);
    }

    /**
     * Test static convert cross-system.
     */
    public function testStaticConvertCrossSystem(): void
    {
        $value = Length::convert(1, 'in', 'cm');

        $this->assertSame(2.54, $value);
    }

    #endregion

    #region Method parse() tests.

    /**
     * Test parsing meters.
     */
    public function testParseMeters(): void
    {
        $length = Length::parse('100 m');

        $this->assertInstanceOf(Length::class, $length);
        $this->assertSame(100.0, $length->value);
        $this->assertSame('m', $length->compoundUnit->asciiSymbol);
    }

    /**
     * Test parsing kilometers.
     */
    public function testParseKilometers(): void
    {
        $length = Length::parse('5.5 km');

        $this->assertSame(5.5, $length->value);
        $this->assertSame('km', $length->compoundUnit->asciiSymbol);
    }

    /**
     * Test parsing feet.
     */
    public function testParseFeet(): void
    {
        $length = Length::parse('6 ft');

        $this->assertSame(6.0, $length->value);
        $this->assertSame('ft', $length->compoundUnit->asciiSymbol);
    }

    /**
     * Test parsing without space.
     */
    public function testParseWithoutSpace(): void
    {
        $length = Length::parse('100m');

        $this->assertSame(100.0, $length->value);
    }

    /**
     * Test parsing scientific notation.
     */
    public function testParseScientificNotation(): void
    {
        $length = Length::parse('1.5e3 m');

        $this->assertSame(1500.0, $length->value);
    }

    #endregion

    #region Chained conversion tests.

    /**
     * Test chained conversions.
     */
    public function testChainedConversions(): void
    {
        $length = new Length(1, 'km');

        // km -> m -> cm -> mm
        $mm = $length->to('m')->to('cm')->to('mm');

        $this->assertSame(1000000.0, $mm->value);
    }

    #endregion

    #region Same unit conversion tests.

    /**
     * Test converting to same unit.
     */
    public function testConvertToSameUnit(): void
    {
        $length = new Length(5, 'm');
        $same = $length->to('m');

        $this->assertSame(5.0, $same->value);
        $this->assertSame('m', $same->compoundUnit->asciiSymbol);
    }

    #endregion

    #region Zero and negative value tests.

    /**
     * Test converting zero value.
     */
    public function testConvertZeroValue(): void
    {
        $length = new Length(0, 'm');
        $km = $length->to('km');

        $this->assertSame(0.0, $km->value);
    }

    /**
     * Test converting negative value.
     */
    public function testConvertNegativeValue(): void
    {
        $length = new Length(-100, 'm');
        $km = $length->to('km');

        $this->assertSame(-0.1, $km->value);
    }

    #endregion
}
