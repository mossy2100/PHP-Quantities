<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Examples;

use Mossy2100\Core\Traits\Asserts\FloatAssertions;
use Mossy2100\Quantities\PhysicalConstant;
use Mossy2100\Quantities\Quantity;
use Mossy2100\Quantities\QuantityType\AmountOfSubstance;
use Mossy2100\Quantities\QuantityType\Mass;
use Mossy2100\Quantities\QuantityType\Pressure;
use Mossy2100\Quantities\QuantityType\Temperature;
use Mossy2100\Quantities\QuantityType\Volume;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * Chemistry examples demonstrating molar and gas law calculations.
 */
#[CoversNothing]
class ChemistryTest extends TestCase
{
    use FloatAssertions;

    /**
     * Ideal gas law: V = nRT/P.
     *
     * Volume of 1 mol of an ideal gas at standard temperature and pressure (0 °C, 1 atm).
     * The molar volume should be approximately 22.414 L.
     */
    public function testIdealGasMolarVolume(): void
    {
        $n = new AmountOfSubstance(1, 'mol');
        $gasConst = PhysicalConstant::molarGas();
        $temp = new Temperature(273.15, 'K');
        $pressure = new Pressure(101325, 'Pa');

        $volume = $n->mul($gasConst)->mul($temp)->div($pressure);

        $this->assertInstanceOf(Volume::class, $volume);
        // nRT/P = 1 × 8.314463 × 273.15 / 101325 ≈ 0.022414 m³ ≈ 22.414 L
        $this->assertApproxEqual(0.022414, $volume->value, 1e-4);
    }

    /**
     * Number of particles: N = n × Nᴀ.
     *
     * Number of molecules in 2 moles of a substance.
     * The result is dimensionless (pure number).
     */
    public function testAvogadroParticleCount(): void
    {
        $n = new AmountOfSubstance(2, 'mol');
        $particleCountA = PhysicalConstant::avogadro();
        $particleCount = $n->mul($particleCountA);

        // mol × mol⁻¹ = dimensionless
        $this->assertInstanceOf(Quantity::class, $particleCount);
        // 2 × 6.02214076 × 10²³ = 1.20443 × 10²⁴
        $this->assertApproxEqual(1.20443e24, $particleCount->value, 1e-4);
    }

    /**
     * Amount of substance from mass and molar mass: n = m/M.
     *
     * How many moles in 18 grams of water (M = 18.015 g/mol)?
     * Molar mass is expressed as kg/mol for SI consistency.
     */
    public function testMolesFromMassAndMolarMass(): void
    {
        $mass = new Mass(0.018, 'kg');
        // Molar mass of water: 18.015 g/mol = 0.018015 kg/mol
        $molarMass = new Mass(0.018015, 'kg')->div(new AmountOfSubstance(1, 'mol'));
        $n = $mass->div($molarMass);

        $this->assertInstanceOf(AmountOfSubstance::class, $n);
        // 0.018 / 0.018015 ≈ 0.99917 mol (≈ 1 mol of water)
        $this->assertApproxEqual(0.99917, $n->value, 1e-4);
    }
}
