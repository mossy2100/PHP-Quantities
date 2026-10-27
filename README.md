# Mossy2100 PHP Quantities

This package enables calculations and conversions with physical and non-physical quantities, including support for SI
and other systems of units (including data and currencies), metric and binary prefixes, parsing, and formatting.

**[License](LICENSE)** | **[Changelog](CHANGELOG.md)** | **[Documentation](docs/)** | **[Examples](docs/WorkingWithQuantities/CalculationExamples.md)**

![PHP 8.4](docs/logo_php8_4.png)

---
## Description

This package provides strongly-typed classes for physical quantities (length, mass, time, temperature, etc.) and some
non-physical quantities (data, money), with comprehensive unit conversion capabilities. The system automatically finds
conversion factors between compatible units, supports metric and binary prefixes, handles offsets for temperature
scales, and loads the latest currency exchange rates automatically.

Key capabilities include:

- **Type-safe measurements**: Each measurement type (Length, Mass, Time, etc.) is a separate class. Plus, you can easily
  create your own custom quantity type classes.
- **Automatic conversion**: Easily convert between any compatible units, with most common units built-in, including SI,
  imperial, US customary, scientific, nautical, CSS, and currencies.
- **Prefix support**: Full support for SI metric and binary prefixes.
- **Arithmetic operations**: Add, subtract, multiply, and divide quantities with automatic unit handling.
- **Flexible parsing**: Parse strings like `123.45 km/h`, `90deg`, or `25°C` into Quantity objects.
- **String formatting**: Format quantities as ASCII or Unicode, with configurable decimal places and locale-specific
  currency formatting.
- **Part decomposition**: Break measurements into components (e.g. `12° 34′ 56″` or `1y 3mo 2d`).
- **Physical constants**: Built-in constants like the speed of light, Planck's constant, and Avogadro's number as
  Quantity objects.
- **Up-to-date exchange rates**: Updated automatically as needed using the exchange rate API of your choice.

---
## Development and Quality Assurance

This package was developed with [Claude Code](https://claude.com/product/claude-code):

- The author developed the initial concept, designed the public API, and did most of the initial coding.
- Claude Code wrote the tests and most of the documentation.
- Claude Code also assisted with multiple code reviews, which led to bug fixes, performance improvements and other
  refinements.

The code is checked with:

- [PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/), for [PSR-12](https://www.php-fig.org/psr/psr-12/)
  compliance plus the Mossy2100 coding standard
- [PHPStan](https://phpstan.org/), at level 9
- [PHPUnit](https://phpunit.de/index.html), with 100% code coverage

![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)

---
## Requirements

- PHP ^8.4
- The `intl` extension, for formatting money
- The `simplexml` extension, for refreshing the currency list
- mossy2100/core

---
## Installation

```bash
composer require mossy2100/quantities
```

---
## Quick start

```php
use Mossy2100\Core\Enums\FloatFormat;
use Mossy2100\Quantities\Currencies\CurrencyService;
use Mossy2100\Quantities\Currencies\ExchangeRateServices\FrankfurterService;
use Mossy2100\Quantities\PhysicalConstant;
use Mossy2100\Quantities\QuantityType\Angle;
use Mossy2100\Quantities\QuantityType\Force;
use Mossy2100\Quantities\QuantityType\Frequency;
use Mossy2100\Quantities\QuantityType\Length;
use Mossy2100\Quantities\QuantityType\Mass;
use Mossy2100\Quantities\QuantityType\Money;
use Mossy2100\Quantities\QuantityType\Temperature;

// Fetch exchange rates when needed: on first use, then whenever they're over an hour old
CurrencyService::init(new FrankfurterService(), autoRefreshTtl: 3600);

// Create measurements
$distance = new Length(5, 'km');
$temp = new Temperature(25, 'degC');
$angle = new Angle(90, 'deg');
$price = new Money(4269, 'EUR');

// Convert between units
$miles = $distance->to('mi');        // 3.10686... miles
$fahrenheit = $temp->to('degF');     // 77°F
$radians = $angle->to('rad');        // 1.5707... rad
$dollars = $price->to('USD');        // e.g. 4621 USD

// Convert to SI units
$force = new Force(28000, 'lbf');
$siForce = $force->toSi()->autoPrefix();  // 124.55... kN
$baseForce = $force->toSiBase();          // 124550.2... kg⋅m/s²

// Arithmetic operations
$total = $distance->add(new Length(500, 'm'));      // 5.5 km
$doubled = $distance->mul(2.0);                     // 10 km
$period = new Frequency(2.4, 'GHz')->recip()->toSi()->autoPrefix(); // 416.7... ps

// Physical constants
$h = PhysicalConstant::planck();
$c = PhysicalConstant::speedOfLight();

// Scientific and engineering calculations
$G = PhysicalConstant::gravitational();
$earthMass = new Mass(5.972e24, 'kg');
$moonMass = new Mass(7.342e22, 'kg');
$earthMoonDistance = new Length(3.844e8, 'm');
$gravity = $G->mul($earthMass)->mul($moonMass)->div($earthMoonDistance->sqr());
echo $gravity->to('N')->format(3, format: FloatFormat::Scientific), "\n"; // 1.98×10²⁰ N

// Parse from strings
$length = Length::parse('123.45 km');
$temp = Temperature::parse('98.6°F');
$angle = Angle::parse("45° 30' 15\"");

// Format as parts
$angle = new Angle(45.5042, 'deg');
echo $angle->formatParts(precision: 1);
// "45° 30′ 15.1″"
```

---
## Concepts

Background on the domain model and how the library represents physical measurements.

1. **[Terminology](docs/Concepts/Terminology.md)** - Key terms and definitions used throughout the library.
2. **[Dimensions and Base Units](docs/Concepts/DimensionsAndBaseUnits.md)** - Dimension codes, base units, and how the library tracks physical dimensions.
3. **[Quantity Types](docs/Concepts/QuantityTypes.md)** - Typed quantity classes like Length, Mass, and Force, and how they map to dimensions.
4. **[Prefixes](docs/Concepts/Prefixes.md)** - Metric, engineering, and binary prefixes for scaling units.
5. **[Units](docs/Concepts/Units.md)** - Unit symbol conventions, notation rules, and supported non-ASCII and alternate symbols.
6. **[Physical Constants](docs/Concepts/PhysicalConstants.md)** - Built-in physical constants as Quantity objects.

---

## Working with Quantities

Practical guides for using the library in your code.

1. **[Creating Quantities](docs/WorkingWithQuantities/CreatingQuantities.md)** - Creating new quantities with constructors and the factory method.
2. **[Unit Conversion](docs/WorkingWithQuantities/UnitConversion.md)** - Converting between units, expansion, simplification, and auto-prefixing.
3. **[Arithmetic Operations](docs/WorkingWithQuantities/ArithmeticOperations.md)** - Add, subtract, multiply, and divide quantities.
4. **[Calculation Examples](docs/WorkingWithQuantities/CalculationExamples.md)** - Real-world physics and engineering calculations.
5. **[Currency Calculations](docs/WorkingWithQuantities/CurrencyCalculations.md)** - Example conversions and calculations involving currencies.
6. **[Comparison Functions](docs/WorkingWithQuantities/ComparisonFunctions.md)** - Exact and approximate equality, ordering, and tolerances.
7. **[String Functions](docs/WorkingWithQuantities/StringFunctions.md)** - Parsing strings into quantities and formatting output (ASCII and Unicode).
8. **[Part Decomposition](docs/WorkingWithQuantities/PartDecomposition.md)** - Working with quantities as parts (e.g. 45° 30′ 15″ or 1h 30min 45s).
9. **[Customization](docs/WorkingWithQuantities/Customization.md)** - Adding custom units, conversions, and quantity type classes.

---
## Reference

### Main Public API

Other than the quantity type classes (below), these are the main classes you'll use.

| Class                                                  | Description                                                                                                                            |
| ------------------------------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------- |
| [Quantity](docs/Reference/Quantity.md)                 | Base class for all measurement types. Provides unit conversion, arithmetic operations, comparison, formatting, and part decomposition. |
| [PhysicalConstant](docs/Reference/PhysicalConstant.md) | Access to physical constants (speed of light, Planck constant, etc.) as Quantity objects.                                              |

### Quantity Types

All quantity type classes extend `Quantity` and define their specific units and conversions. See the
**[Quantity Type reference](docs/Reference/QuantityType/)** for the complete list of built-in units for each type.

| Class                                                                     | Dimension  | SI or common base unit | Description                                      |
| ------------------------------------------------------------------------- | ---------- | ---------------------- | ------------------------------------------------ |
| [Acceleration](docs/Reference/QuantityType/Acceleration.md)               | LT-2       | m/s²                   | Rate of change of velocity.                      |
| [AmountOfSubstance](docs/Reference/QuantityType/AmountOfSubstance.md)     | N          | mol                    | SI base quantity for counting entities.          |
| [Angle](docs/Reference/QuantityType/Angle.md)                             | A          | rad                    | Angular measurements with trig functions.        |
| [Area](docs/Reference/QuantityType/Area.md)                               | L2         | m²                     | Two-dimensional extent.                          |
| [Capacitance](docs/Reference/QuantityType/Capacitance.md)                 | M-1L-2T4I2 | F                      | Ability to store electric charge.                |
| [CatalyticActivity](docs/Reference/QuantityType/CatalyticActivity.md)     | T-1N       | kat                    | Rate of catalysis.                               |
| [Conductance](docs/Reference/QuantityType/Conductance.md)                 | M-1L-2T3I2 | S                      | Electrical conductance.                          |
| [Data](docs/Reference/QuantityType/Data.md)                               | D          | B                      | Digital storage with metric and binary prefixes. |
| [Density](docs/Reference/QuantityType/Density.md)                         | ML-3       | kg/m³                  | Mass per unit volume.                            |
| [Dimensionless](docs/Reference/QuantityType/Dimensionless.md)             | *empty*    | *empty*                | Ratios, percentages, and pure numbers.           |
| [ElectricCharge](docs/Reference/QuantityType/ElectricCharge.md)           | TI         | C                      | Quantity of electricity.                         |
| [ElectricCurrent](docs/Reference/QuantityType/ElectricCurrent.md)         | I          | A                      | SI base quantity for electric current.           |
| [Energy](docs/Reference/QuantityType/Energy.md)                           | ML2T-2     | J                      | Capacity to do work.                             |
| [Force](docs/Reference/QuantityType/Force.md)                             | MLT-2      | N                      | Interaction causing acceleration.                |
| [Frequency](docs/Reference/QuantityType/Frequency.md)                     | T-1        | Hz, Bq                 | Cycles per unit time.                            |
| [Illuminance](docs/Reference/QuantityType/Illuminance.md)                 | L-2A2J     | lx                     | Luminous flux per area.                          |
| [Inductance](docs/Reference/QuantityType/Inductance.md)                   | ML2T-2I-2  | H                      | Property opposing current change.                |
| [Length](docs/Reference/QuantityType/Length.md)                           | L          | m                      | SI base quantity for distance.                   |
| [LuminousFlux](docs/Reference/QuantityType/LuminousFlux.md)               | A2J        | lm                     | Perceived light power.                           |
| [LuminousIntensity](docs/Reference/QuantityType/LuminousIntensity.md)     | J          | cd                     | SI base quantity for luminous intensity.         |
| [MagneticFlux](docs/Reference/QuantityType/MagneticFlux.md)               | ML2T-2I-1  | Wb                     | Total magnetic field through surface.            |
| [MagneticFluxDensity](docs/Reference/QuantityType/MagneticFluxDensity.md) | MT-2I-1    | T                      | Magnetic field strength.                         |
| [Mass](docs/Reference/QuantityType/Mass.md)                               | M          | kg                     | SI base quantity for mass.                       |
| [Money](docs/Reference/QuantityType/Money.md)                             | C          | XAU                    | Currency conversions and calculations.           |
| [Power](docs/Reference/QuantityType/Power.md)                             | ML2T-3     | W                      | Rate of energy transfer.                         |
| [Pressure](docs/Reference/QuantityType/Pressure.md)                       | ML-1T-2    | Pa                     | Force per unit area.                             |
| [RadiationDose](docs/Reference/QuantityType/RadiationDose.md)             | L2T-2      | Gy, Sv                 | Absorbed and equivalent radiation dose.          |
| [Resistance](docs/Reference/QuantityType/Resistance.md)                   | ML2T-3I-2  | Ω                      | Opposition to electric current.                  |
| [SolidAngle](docs/Reference/QuantityType/SolidAngle.md)                   | A2         | sr                     | Three-dimensional angular extent.                |
| [Temperature](docs/Reference/QuantityType/Temperature.md)                 | H          | K                      | SI base quantity with affine conversions.        |
| [Time](docs/Reference/QuantityType/Time.md)                               | T          | s                      | SI base quantity for duration.                   |
| [Velocity](docs/Reference/QuantityType/Velocity.md)                       | LT-1       | m/s                    | Rate of change of position.                      |
| [Voltage](docs/Reference/QuantityType/Voltage.md)                         | ML2T-3I-1  | V                      | Electric potential difference.                   |
| [Volume](docs/Reference/QuantityType/Volume.md)                           | L3         | m³                     | Three-dimensional extent.                        |

### Services

These classes are predominantly internal, except for adding custom units via `UnitService::add()`, or registering new
quantity types via `QuantityTypeService::add()`.

| Class                                                                 | Description                                                                                    |
| --------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------- |
| [ConversionService](docs/Reference/Services/ConversionService.md)     | Manages unit conversions and converters.                                                       |
| [DimensionService](docs/Reference/Services/DimensionService.md)       | Utilities for working with physical dimension codes (validation, composition, transformation). |
| [PrefixService](docs/Reference/Services/PrefixService.md)             | Manages SI and binary prefixes (lookup, filtering by group).                                   |
| [QuantityTypeService](docs/Reference/Services/QuantityTypeService.md) | Registry of quantity types keyed by name.                                                      |
| [QuantityType](docs/Reference/QuantityType.md)                        | Data class for a registered quantity type: name, dimension and class.                          |
| [UnitService](docs/Reference/Services/UnitService.md)                 | Registry of units with lookup, filtering, and loading by system.                               |

### Units

Classes for units and conversions. You'll use these to add custom units and conversions (see [Customization](docs/WorkingWithQuantities/Customization.md)).

| Name                                                   | Description                                                                |
| ------------------------------------------------------ | -------------------------------------------------------------------------- |
| [Unit](docs/Reference/Units/Unit.md)                   | Represents a single-symbol measurement unit with optional prefix support.  |
| [UnitTerm](docs/Reference/Units/UnitTerm.md)           | A unit with optional prefix and exponent (e.g., km², ms⁻¹).                |
| [CompoundUnit](docs/Reference/Units/CompoundUnit.md)   | Compound unit expression combining unit terms via multiplication/division. |
| [Prefix](docs/Reference/Units/Prefix.md)               | SI metric and binary prefixes (kilo, mega, kibi, etc.).                    |
| [Conversion](docs/Reference/Units/Conversion.md)       | Represents a unit conversion with factor and error tracking.               |
| [UnitInterface](docs/Reference/Units/UnitInterface.md) | Interface implemented by Unit, UnitTerm, and CompoundUnit.                 |

### Enums

| Name                                             | Description                                                   |
| ------------------------------------------------ | ------------------------------------------------------------- |
| [UnitSystem](docs/Reference/Enums/UnitSystem.md) | Enum for systems of units (SI, Imperial, US Customary, etc.). |

### Internal Types

These classes are used by the conversion engine and aren't part of the public API. They may change without notice.

| Name                                                        | Description                                                                |
| ----------------------------------------------------------- | -------------------------------------------------------------------------- |
| [Converter](docs/Reference/Internal/Converter.md)           | Graph-based algorithm for finding conversion paths between units.          |
| [FloatWithError](docs/Reference/Internal/FloatWithError.md) | Floating-point numbers with tracked error bounds for precision monitoring. |

### Exceptions

Custom exception types to provide additional context when needed. Both extend `DomainException`, as they relate to
provided values being outside a valid domain.

| Class                                                                                 | Description                                     |
| ------------------------------------------------------------------------------------- | ----------------------------------------------- |
| [UnknownUnitException](docs/Reference/Exceptions/UnknownUnitException.md)             | Thrown when a unit symbol cannot be resolved.   |
| [DimensionMismatchException](docs/Reference/Exceptions/DimensionMismatchException.md) | Thrown when units have incompatible dimensions. |

### Currency Classes

Classes for currency data management and exchange rate integration.

| Class                                                                                                          | Description                                                 |
| -------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------- |
| [CurrencyService](docs/Reference/Currencies/CurrencyService.md)                                                | Manages currency unit data and exchange rate conversions.   |
| [ExchangeRateServiceInterface](docs/Reference/Currencies/ExchangeRateServices/ExchangeRateServiceInterface.md) | Contract for exchange rate providers.                       |
| [CurrencyLayerService](docs/Reference/Currencies/ExchangeRateServices/CurrencyLayerService.md)                 | CurrencyLayer API (USD-based, 1,000 req/month free).        |
| [ExchangeRateApiService](docs/Reference/Currencies/ExchangeRateServices/ExchangeRateApiService.md)             | ExchangeRate-API (any base currency, 1,500 req/month free). |
| [FixerService](docs/Reference/Currencies/ExchangeRateServices/FixerService.md)                                 | Fixer.io API (EUR-based, 10,000 req/month free).            |
| [FrankfurterService](docs/Reference/Currencies/ExchangeRateServices/FrankfurterService.md)                     | Frankfurter API (ECB data, completely free, no API key).    |
| [OpenExchangeRatesService](docs/Reference/Currencies/ExchangeRateServices/OpenExchangeRatesService.md)         | Open Exchange Rates API (USD-based, free tier available).   |

---

## Testing

There are two test suites:

- `unit` (default): fast, offline tests in `tests/NonCurrencies`.
- `currencies`: tests in `tests/Currencies` that call live exchange rate APIs. They need API keys in
  `tests/Currencies/api-keys.json` (see `api-keys.json.example`) and use up request quota.

```bash
# Run the unit tests
vendor/bin/phpunit

# Run a specific test file
vendor/bin/phpunit tests/NonCurrencies/Quantity/QuantityArithmeticTest.php

# Run tests matching a filter
vendor/bin/phpunit --filter=testCompare

# Run the live currency tests
vendor/bin/phpunit --testsuite currencies

# Run with coverage (generates HTML report and clover.xml): unit, currencies or all
composer test all
```

---

## License

MIT License - see [LICENSE](LICENSE) for details

---

## Support

- **Issues**: https://github.com/mossy2100/PHP-Quantities/issues
- **Documentation**: See [docs/](docs/) directory for detailed class documentation

For questions or suggestions, please [open an issue](https://github.com/mossy2100/PHP-Quantities/issues).

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and changes.
