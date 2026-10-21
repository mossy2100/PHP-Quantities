<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Examples;

use Mossy2100\Core\Traits\Asserts\FloatAssertions;
use Mossy2100\Quantities\QuantityType\Capacitance;
use Mossy2100\Quantities\QuantityType\ElectricCharge;
use Mossy2100\Quantities\QuantityType\ElectricCurrent;
use Mossy2100\Quantities\QuantityType\Energy;
use Mossy2100\Quantities\QuantityType\Frequency;
use Mossy2100\Quantities\QuantityType\Power;
use Mossy2100\Quantities\QuantityType\Resistance;
use Mossy2100\Quantities\QuantityType\Time;
use Mossy2100\Quantities\QuantityType\Voltage;
use PHPUnit\Framework\TestCase;

/**
 * Electromagnetism examples demonstrating electrical circuit calculations.
 */
class ElectromagnetismTest extends TestCase
{
    use FloatAssertions;

    /**
     * Ohm's law: V = IR.
     *
     * A current of 2 A through a 100 Ω resistor.
     */
    public function testOhmsLaw(): void
    {
        $current = new ElectricCurrent(2, 'A');
        $resistance = new Resistance(100, 'ohm');
        $voltage = $current->mul($resistance);

        $this->assertInstanceOf(Voltage::class, $voltage);
        $this->assertApproxEqual(200.0, $voltage->value);
    }

    /**
     * Electrical power: P = IV.
     *
     * A 2 A current at 120 V (typical US household appliance).
     */
    public function testElectricalPower(): void
    {
        $current = new ElectricCurrent(2, 'A');
        $voltage = new Voltage(120, 'V');
        $power = $current->mul($voltage);

        $this->assertInstanceOf(Power::class, $power);
        $this->assertApproxEqual(240.0, $power->value);
    }

    /**
     * Electric charge: Q = It.
     *
     * A current of 0.5 A flowing for 60 seconds.
     */
    public function testChargeFromCurrent(): void
    {
        $current = new ElectricCurrent(0.5, 'A');
        $t = new Time(60, 's');
        $charge = $current->mul($t);

        $this->assertInstanceOf(ElectricCharge::class, $charge);
        $this->assertApproxEqual(30.0, $charge->value);
    }

    /**
     * Energy stored in a capacitor: E = ½CV².
     *
     * A 10 µF capacitor charged to 12 V.
     */
    public function testCapacitorEnergy(): void
    {
        $cap = new Capacitance(10e-6, 'F');
        $voltage = new Voltage(12, 'V');
        $energy = $cap->mul($voltage->sqr())->mul(0.5);

        $this->assertInstanceOf(Energy::class, $energy);
        // ½ × 10×10⁻⁶ × 144 = 7.2 × 10⁻⁴ J
        $this->assertApproxEqual(7.2e-4, $energy->value);
    }

    /**
     * AC mains period (Europe/Australia): T = 1/f.
     *
     * Period of 50 Hz mains electricity.
     */
    public function testAcMainsPeriod50Hz(): void
    {
        $mains = new Frequency(50, 'Hz');
        $period = $mains->recip();

        $this->assertInstanceOf(Time::class, $period);
        // 1/50 = 0.02 s = 20 ms
        $ms = $period->to('ms');
        $this->assertSame(20.0, $ms->value);
    }

    /**
     * AC mains period (North America): T = 1/f.
     *
     * Period of 60 Hz mains electricity.
     */
    public function testAcMainsPeriod60Hz(): void
    {
        $mains = new Frequency(60, 'Hz');
        $period = $mains->recip();

        $this->assertInstanceOf(Time::class, $period);
        // 1/60 ≈ 16.67 ms
        $ms = $period->to('ms');
        $this->assertApproxEqual(1000.0 / 60, $ms->value);
    }
}
