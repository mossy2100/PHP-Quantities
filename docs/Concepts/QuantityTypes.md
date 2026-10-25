# Quantity Types

A [quantity type](https://en.wikipedia.org/wiki/Quantity) is a category of measurable property — such as length, mass, or time — that defines what kind of thing is being measured.

The package provides several classes to support quantity types:

1. The `Quantity` base class, which provides most of the functionality for quantity conversion, arithmetic, comparison, etc.
2. The `Quantity` subclasses (`Time`, `Length`, `Force`, etc.), each of which represents a quantity type. See below.
3. The `QuantityType` class, which encapsulates a quantity type.
4. The `QuantityTypeService`, which functions as a registry linking dimensions to quantity type classes (i.e. `Quantity` subclasses).

---

## SI base quantity types

All seven [SI base quantity types](https://en.wikipedia.org/wiki/SI_base_unit) have a dedicated class:

| Name                | Dimension | Class                                                                 | SI base unit |
| ------------------- | --------- | --------------------------------------------------------------------- | ------------ |
| length              | `L`       | [`Length`](../Reference/QuantityType/Length.md)                       | meter (*m*)             |
| mass                | `M`       | [`Mass`](../Reference/QuantityType/Mass.md)                           | kilogram (*kg*)         |
| time                | `T`       | [`Time`](../Reference/QuantityType/Time.md)                           | second (*s*)            |
| electric current    | `I`       | [`ElectricCurrent`](../Reference/QuantityType/ElectricCurrent.md)     | ampere (*A*)            |
| temperature         | `H`       | [`Temperature`](../Reference/QuantityType/Temperature.md)             | kelvin (*K*)            |
| amount of substance | `N`       | [`AmountOfSubstance`](../Reference/QuantityType/AmountOfSubstance.md) | mole (*mol*)            |
| luminous intensity  | `J`       | [`LuminousIntensity`](../Reference/QuantityType/LuminousIntensity.md) | candela (*cd*)          |

---

## Quantity types for SI named units

The package covers all 22 [named SI derived units](https://en.wikipedia.org/wiki/SI_derived_unit#Special_names). Three share a class with a dimensionally equivalent quantity type: becquerel with `Frequency` (both T⁻¹), sievert with `RadiationDose` (both T⁻²L²), and degree Celsius with `Temperature`. The remaining 19 have dedicated classes:

| Name                  | Dimension    | Class                                                                     | SI derived unit  |
| --------------------- | ------------ | ------------------------------------------------------------------------- | ---------------- |
| angle                 | `A`          | [`Angle`](../Reference/QuantityType/Angle.md)                             | radian (*rad*)   |
| solid angle           | `A2`         | [`SolidAngle`](../Reference/QuantityType/SolidAngle.md)                   | steradian (*sr*) |
| frequency             | `T-1`        | [`Frequency`](../Reference/QuantityType/Frequency.md)                     | hertz (*Hz*)     |
| force                 | `MLT-2`      | [`Force`](../Reference/QuantityType/Force.md)                             | newton (*N*)     |
| pressure              | `ML-1T-2`    | [`Pressure`](../Reference/QuantityType/Pressure.md)                       | pascal (*Pa*)    |
| energy                | `ML2T-2`     | [`Energy`](../Reference/QuantityType/Energy.md)                           | joule (*J*)      |
| power                 | `ML2T-3`     | [`Power`](../Reference/QuantityType/Power.md)                             | watt (*W*)       |
| electric charge       | `TI`         | [`ElectricCharge`](../Reference/QuantityType/ElectricCharge.md)           | coulomb (*C*)    |
| voltage               | `ML2T-3I-1`  | [`Voltage`](../Reference/QuantityType/Voltage.md)                         | volt (*V*)       |
| capacitance           | `M-1L-2T4I2` | [`Capacitance`](../Reference/QuantityType/Capacitance.md)                 | farad (*F*)      |
| resistance            | `ML2T-3I-2`  | [`Resistance`](../Reference/QuantityType/Resistance.md)                   | ohm (*Ω*)        |
| conductance           | `M-1L-2T3I2` | [`Conductance`](../Reference/QuantityType/Conductance.md)                 | siemens (*S*)    |
| magnetic flux         | `ML2T-2I-1`  | [`MagneticFlux`](../Reference/QuantityType/MagneticFlux.md)               | weber (*Wb*)     |
| magnetic flux density | `MT-2I-1`    | [`MagneticFluxDensity`](../Reference/QuantityType/MagneticFluxDensity.md) | tesla (*T*)      |
| inductance            | `ML2T-2I-2`  | [`Inductance`](../Reference/QuantityType/Inductance.md)                   | henry (*H*)      |
| luminous flux         | `A2J`        | [`LuminousFlux`](../Reference/QuantityType/LuminousFlux.md)               | lumen (*lm*)     |
| illuminance           | `L-2A2J`     | [`Illuminance`](../Reference/QuantityType/Illuminance.md)                 | lux (*lx*)       |
| absorbed dose         | `L2T-2`      | [`RadiationDose`](../Reference/QuantityType/RadiationDose.md)             | gray (*Gy*)      |
| catalytic activity    | `T-1N`       | [`CatalyticActivity`](../Reference/QuantityType/CatalyticActivity.md)     | katal (*kat*)    |

---

## Additional built-in quantity type classes

These commonly used quantity types also have dedicated classes:

| Name          | Dimension | Class                                                         | SI derived or common base unit     |
| ------------- | --------- | ------------------------------------------------------------- | ---------------------------------- |
| dimensionless | *(empty)* | [`Dimensionless`](../Reference/QuantityType/Dimensionless.md) | *(none)*                           |
| data          | `D`       | [`Data`](../Reference/QuantityType/Data.md)                   | byte (*B*)                         |
| money         | `C`       | [`Money`](../Reference/QuantityType/Money.md)                 | troy ounce of gold (*XAU*)         |
| area          | `L2`      | [`Area`](../Reference/QuantityType/Area.md)                   | square meter (*m²*)                |
| volume        | `L3`      | [`Volume`](../Reference/QuantityType/Volume.md)               | cubic meter (*m³*)                 |
| velocity      | `LT-1`    | [`Velocity`](../Reference/QuantityType/Velocity.md)           | meter per second (*m/s*)           |
| acceleration  | `LT-2`    | [`Acceleration`](../Reference/QuantityType/Acceleration.md)   | meter per second² (*m/s²*)         |
| density       | `ML-3`    | [`Density`](../Reference/QuantityType/Density.md)             | kilogram per cubic meter (*kg/m³*) |

---

## Quantity types without classes

There are many other quantity types for which no dedicated subclass is provided, as doing so would unnecessarily bloat the package.

Examples include: momentum, angular velocity, viscosity, electric field strength, and heat capacity. Many more can be found at the [list of physical quantities](https://en.wikipedia.org/wiki/List_of_physical_quantities) on Wikipedia.

Only two quantity type classes (`Acceleration` and `Density`) are included that add no additional functionality, as they have no dedicated units or conversions. They're included because they're reasonably common, and serve as examples of how to add your own custom quantity types.

Using dedicated subclasses for quantity types improves code readability and type safety, and can be a form of future-proofing in case additional functionality needs to be added later. However, this isn't a requirement, as the base `Quantity` class works for any quantity type.

---

## See also

- **[Creating Quantities](../WorkingWithQuantities/CreatingQuantities.md)** — How to instantiate quantities using constructors and the factory method.
- **[Customization](../WorkingWithQuantities/Customization.md)** — How to create your own custom quantity type classes.
- **[Units](Units.md)** — Unit symbol conventions, notation rules, and supported non-ASCII and alternate symbols.
- **[QuantityTypeService](../Reference/Services/QuantityTypeService.md)** — Service for registering and looking up quantity types.
- **[QuantityType](../Reference/QuantityType.md)** — Metadata class representing a quantity type.

