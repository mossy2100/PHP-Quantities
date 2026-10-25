# QuantityTypeService

Registry of quantity types keyed by name.

**Namespace:** `Mossy2100\Quantities\Services`

---

## Overview

The `QuantityTypeService` provides mapping between:

- Dimension codes (e.g. `'L'`, `'M'`, `'ML2T-2'`)
- Quantity names (e.g. `'length'`, `'mass'`, `'energy'`)
- PHP classes (e.g. `Length::class`, `Mass::class`, `Energy::class`)

This allows `Quantity::create()` to instantiate the appropriate subclass based on dimensional analysis. For example, when multiplying `Length * Length`, `create()` returns an `Area` object because dimension `L2` maps to `Area::class`.

All methods are static. The class uses lazy initialization to build the registry on first access.

---

## Constants

### `DEFAULT_QUANTITY_TYPES`

Built-in quantity type definitions keyed by name. Each entry contains a dimension code and PHP class. The registry includes all standard physical quantities:

| Name | Dimension | Class |
|---|---|---|
| dimensionless | *(empty)* | `Dimensionless` |
| length | L | `Length` |
| mass | M | `Mass` |
| time | T | `Time` |
| electric current | I | `ElectricCurrent` |
| temperature | H | `Temperature` |
| amount of substance | N | `AmountOfSubstance` |
| luminous intensity | J | `LuminousIntensity` |
| angle | A | `Angle` |
| data | D | `Data` |
| money | C | `Money` |
| solid angle | A2 | `SolidAngle` |
| area | L2 | `Area` |
| volume | L3 | `Volume` |
| velocity | LT-1 | `Velocity` |
| acceleration | LT-2 | `Acceleration` |
| density | ML-3 | `Density` |
| frequency | T-1 | `Frequency` |
| force | MLT-2 | `Force` |
| pressure | ML-1T-2 | `Pressure` |
| energy | ML2T-2 | `Energy` |
| power | ML2T-3 | `Power` |
| electric charge | TI | `ElectricCharge` |
| voltage | ML2T-3I-1 | `Voltage` |
| capacitance | M-1L-2T4I2 | `Capacitance` |
| resistance | ML2T-3I-2 | `Resistance` |
| conductance | M-1L-2T3I2 | `Conductance` |
| magnetic flux | ML2T-2I-1 | `MagneticFlux` |
| magnetic flux density | MT-2I-1 | `MagneticFluxDensity` |
| inductance | ML2T-2I-2 | `Inductance` |
| luminous flux | A2J | `LuminousFlux` |
| illuminance | L-2A2J | `Illuminance` |
| absorbed dose | L2T-2 | `RadiationDose` |
| catalytic activity | T-1N | `CatalyticActivity` |

---

## Lookup methods

### getAll()

```php
public static function getAll(): array
```

Get all registered quantity types.

**Returns:** `array<string, QuantityType>` — Keyed by quantity type name.

```php
$allTypes = QuantityTypeService::getAll();
foreach ($allTypes as $name => $qtyType) {
    echo "$name: {$qtyType->dimension}\n";
}
```

### getByDimension()

```php
public static function getByDimension(string $dimension): ?QuantityType
```

Get the quantity type matching a given dimension code. The dimension code is normalized before lookup.

**Returns:** `?QuantityType`

**Throws:** [`FormatException`](https://github.com/mossy2100/PHP-Core/blob/main/docs/Exceptions/FormatException.md) if the dimension code is invalid.

```php
$qtyType = QuantityTypeService::getByDimension('L');
echo $qtyType->name;      // 'length'
echo $qtyType->dimension;  // 'L'
echo $qtyType->class;      // 'Mossy2100\Quantities\QuantityType\Length'

// Normalizes dimension codes.
$qtyType = QuantityTypeService::getByDimension('LT-1');
echo $qtyType->name;  // 'velocity'

// Returns null for unregistered dimensions.
$qtyType = QuantityTypeService::getByDimension('L4');  // null
```

### getByName()

```php
public static function getByName(string $name): ?QuantityType
```

Get the quantity type matching a given name. Case-insensitive.

**Returns:** `?QuantityType`

```php
$qtyType = QuantityTypeService::getByName('energy');
echo $qtyType->dimension;  // 'ML2T-2'

$qtyType = QuantityTypeService::getByName('Electric Current');  // case-insensitive
echo $qtyType->dimension;  // 'I'

QuantityTypeService::getByName('nonexistent');  // null
```

### getByClass()

```php
public static function getByClass(string $class): ?QuantityType
```

Get the quantity type matching a given PHP class.

**Returns:** `?QuantityType`

```php
$qtyType = QuantityTypeService::getByClass(Force::class);
echo $qtyType->name;       // 'force'
echo $qtyType->dimension;  // 'MLT-2'

QuantityTypeService::getByClass(Quantity::class);  // null (base class not registered)
```

### getClasses()

```php
public static function getClasses(): array
```

Get all registered quantity type classes.

**Returns:** `list<class-string<Quantity>>`

```php
$classes = QuantityTypeService::getClasses();
// [Dimensionless::class, Length::class, Mass::class, ...]
```

---

## Registry methods

### add()

```php
public static function add(string $name, string $dimension, string $class): void
```

Register a new quantity type. The name, dimension, and class must all be unique.

**Throws:**
- [`FormatException`](https://github.com/mossy2100/PHP-Core/blob/main/docs/Exceptions/FormatException.md) if the dimension code is invalid.
- `LogicException` if the name, dimension, or class is already registered.

```php
QuantityTypeService::add('jerk', 'LT-3', Jerk::class);

// Now Quantity::create() will return Jerk for dimension LT-3.
$jerk = Quantity::create(1, 'm/s3');
// $jerk instanceof Jerk === true
```

See **[Customization](../../WorkingWithQuantities/Customization.md)** for full details on adding custom quantity types.

### setClass()

```php
public static function setClass(string $name, string $class): void
```

Set or update the class for an existing quantity type.

**Throws:** `DomainException` if the quantity type name is not registered.

```php
QuantityTypeService::setClass('money', CustomMoney::class);
```

### remove()

```php
public static function remove(string $name): void
```

Remove a quantity type from the registry. Does nothing if the name doesn't exist or the registry is not initialized.

```php
QuantityTypeService::remove('jerk');
```

### removeAll()

```php
public static function removeAll(): void
```

Remove all quantity types from the registry. Unlike `reset()`, the next access will NOT trigger re-initialization.

### reset()

```php
public static function reset(): void
```

Reset the registry to its initial state, forcing re-initialization from the built-in definitions on next access. Useful for test isolation.

---

## See also

- **[QuantityType](../QuantityType.md)** - QuantityType class documentation
- **[Quantity](../Quantity.md)** - Base quantity class
- **[DimensionService](DimensionService.md)** - Dimension code utilities
- **[UnitService](UnitService.md)** - Unit registry
- **[Customization](../../WorkingWithQuantities/Customization.md)** - Adding custom quantity types and units
