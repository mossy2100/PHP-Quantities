# Money

Represents money quantities with currency conversion and locale-aware formatting.

**Namespace:** `Mossy2100\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Money` class handles monetary values with support for all [ISO 4217](https://en.wikipedia.org/wiki/ISO_4217)
currencies. Unlike other quantity types, its units and conversion rates come from
[`CurrencyService`](../Currencies/CurrencyService.md) rather than being hard-coded.

All currency units belong to the `Financial` unit system. The base unit is `XAU` (gold troy ounces).

Currency units work out of the box, e.g. `new Money(5, 'USD')`. Converting between currencies needs exchange rates,
so configure an exchange rate service, then either fetch the rates or turn on auto-refresh:

```php
use Mossy2100\Quantities\Currencies\CurrencyService;
use Mossy2100\Quantities\Currencies\ExchangeRateServices\FrankfurterService;

// Refetch the rates whenever they're more than an hour old.
CurrencyService::init(new FrankfurterService(), autoRefreshTtl: 3600);

// Or fetch them now, and again whenever you choose.
CurrencyService::init(new FrankfurterService());
CurrencyService::refreshRates();
```

See [CurrencyService](../Currencies/CurrencyService.md) for setup details.

---

## Unit definitions

Currency units come from the official [ISO 4217](https://en.wikipedia.org/wiki/ISO_4217) list published by SIX
Group, which defines currency names and three-letter codes (e.g. `USD`, `EUR`, `JPY`). A snapshot of the list is
bundled with the package, and `CurrencyService::refreshUnits()` fetches a fresh copy.

See [`CurrencyService::getUnits()`](../Currencies/CurrencyService.md#getunits) for details.

---

## Conversion definitions

Exchange rate conversions come from the local rates, which are fetched from a pluggable exchange rate service by
`CurrencyService::refreshRates()` or by auto-refresh. Each service adapter returns its rates as conversion definition
tuples, the same format used by other quantity types' `getConversionDefinitions()`. Until rates have been fetched,
there are none.

The available exchange rate services are documented in [CurrencyService](../Currencies/CurrencyService.md#exchange-rate-services).

---

## Overridden methods

### \_\_toString()

```php
public function __toString(): string
```

Convert the currency value to a locale-specific formatted string using PHP's `NumberFormatter`. Falls back to `format()` if no locale is available.

The locale can be set via `CurrencyService::init()` or changed later with `CurrencyService::setLocale()`. If no locale is set, it is auto-detected from the HTTP `Accept-Language` header or PHP's default locale.

```php
CurrencyService::setLocale('en_US');
$price = new Money(1234.56, 'USD');
echo $price;  // $1,234.56

CurrencyService::setLocale('de_DE');
$price = new Money(1234.56, 'EUR');
echo $price;  // 1.234,56 €
```

---

## Usage examples

```php
use Mossy2100\Quantities\QuantityType\Money;

// Create money values.
$price = new Money(100, 'USD');
$salary = new Money(75000, 'EUR');

// Convert between currencies.
echo $salary->to('USD');  // e.g. 81000 USD
echo $salary->to('JPY');  // e.g. 11287500 JPY
echo $salary->to('GBP');  // e.g. 63750 GBP

// String output uses locale-aware formatting.
echo $price;  // $100.00

// Use format() for the standard Quantity format.
echo $price->format();  // 100 USD
```

---

## See also

- **[CurrencyService](../Currencies/CurrencyService.md)** — Currency data management, setup, and locale configuration.
- **[ExchangeRateServiceInterface](../Currencies/ExchangeRateServices/ExchangeRateServiceInterface.md)** — Contract for exchange rate providers.
- **[Quantity](../Quantity.md)** — Base class documentation.
- **[UnitSystem](../Enums/UnitSystem.md)** — Unit system classification (Financial system).
