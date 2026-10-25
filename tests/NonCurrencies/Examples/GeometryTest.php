<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Tests\NonCurrencies\Examples;

use Mossy2100\Core\Traits\Asserts\FloatAssertions;
use Mossy2100\Quantities\QuantityType\Area;
use Mossy2100\Quantities\QuantityType\Length;
use Mossy2100\Quantities\QuantityType\Volume;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * Geometry examples demonstrating area and volume calculations using the Quantities package.
 */
#[CoversNothing]
class GeometryTest extends TestCase
{
    use FloatAssertions;

    /**
     * Circumference of a circle: C = 2πr.
     */
    public function testCircleCircumference(): void
    {
        $r = new Length(5, 'm');
        $circumference = $r->mul(2 * M_PI);

        $this->assertInstanceOf(Length::class, $circumference);
        $this->assertApproxEqual(31.41592653589793, $circumference->value);
    }

    /**
     * Area of a circle: A = πr².
     */
    public function testCircleArea(): void
    {
        $r = new Length(5, 'm');
        $area = $r->sqr()->mul(M_PI);

        $this->assertInstanceOf(Area::class, $area);
        $this->assertApproxEqual(78.53981633974483, $area->value);
    }

    /**
     * Surface area of a sphere: A = 4πr².
     */
    public function testSphereSurfaceArea(): void
    {
        $r = new Length(5, 'm');
        $area = $r->sqr()->mul(4 * M_PI);

        $this->assertInstanceOf(Area::class, $area);
        $this->assertApproxEqual(314.1592653589793, $area->value);
    }

    /**
     * Volume of a sphere: V = (4/3)πr³.
     */
    public function testSphereVolume(): void
    {
        $r = new Length(5, 'm');
        $volume = $r->pow(3)->mul(4 / 3 * M_PI);

        $this->assertInstanceOf(Volume::class, $volume);
        $this->assertApproxEqual(523.5987755982989, $volume->value);
    }
}
