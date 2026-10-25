# CurrencyService

Static service for managing currency units and exchange rate conversions.

**Namespace:** `Mossy2100\Quantities\Currencies`

---

## Overview

The `CurrencyService` manages the data behind [`Money`](../QuantityType/Money.md) quantities:

- Currency units, from the official ISO 4217 list (published by SIX Group).
- Exchange rates, from a pluggable `ExchangeRateServiceInterface`.
- The locale used to format money.

Currency units are registered under the `UnitSystem::Financial` unit system.

### Local data, and when it's fetched

Currencies always work from local data. **Nothing is fetched unless you ask for it**, or turn on auto-refresh:

| Data           | Comes from                                                                | Fetched by       |
| -------------- | ------------------------------------------------------------------------- | ---------------- |
| Currency units | The data directory if refreshed, else a snapshot bundled with the package | `refreshUnits()` |
| Exchange rates | The data directory, once fetched                                          | `refreshRates()` |

So `new Money(5, 'USD')` works out of the box. Until rates have been fetched, converting between currencies fails like
any conversion with no known path (a `LogicException`). The ISO 4217 list changes rarely, so `refreshUnits()` only needs
calling occasionally.

### Auto-refresh

Auto-refresh is off by default. Turn it on by passing a TTL in seconds to `init()` or `setAutoRefreshTtl()`. A currency
conversion then refetches the rates first if they're stale, i.e.:

- there are no local rates, or
- they're older than the TTL, or
- they came from a different service than the one configured.

If that fetch fails, the conversion throws `RuntimeException` rather than using the old rates. The staleness check uses
metadata held in memory, so it's cheap. Currency units are never refreshed automatically.

### Data directory

Refreshed data is written to a data directory as PHP files. The default is `mossy2100-quantities` in the system temp
directory. Temp directories can be cleared, so it's best to call `setDataDir()` in production.

> [!WARNING]
> The data files are PHP that the service `require`s. Make sure the data directory is not writable by anyone untrusted.

### Exchange rate services

The package includes adapters for several exchange rate APIs, all of which have free tiers:

| Service                    | API key required | Approx. currencies | Website                                                   |
| -------------------------- | ---------------- | ------------------ | --------------------------------------------------------- |
| `FrankfurterService`       | No               | 30                 | [frankfurter.dev](https://frankfurter.dev/)               |
| `ExchangeRateApiService`   | Yes              | 160                | [exchangerate-api.com](https://www.exchangerate-api.com/) |
| `OpenExchangeRatesService` | Yes              | 170                | [openexchangerates.org](https://openexchangerates.org/)   |
| `CurrencyLayerService`     | Yes              | 170                | [currencylayer.com](https://currencylayer.com/)           |
| `FixerService`             | Yes              | 170                | [fixer.io](https://fixer.io/)                             |

All services are in the `Mossy2100\Quantities\Currencies\ExchangeRateServices` namespace.

`FrankfurterService` is the simplest option: no API key, no signup. It uses European Central Bank data and covers about
30 major currencies. The other services need a free API key, but support 160+ currencies, including precious metals
(XAU, XAG) and some cryptocurrencies.

---

## Main methods

### init()

```php
public static function init(
    ExchangeRateServiceInterface $exchangeRateService,
    ?string $locale = null,
    ?int $autoRefreshTtl = null
): void
```

Initialize the currency service. This only sets the configuration; nothing is fetched.

**Parameters:**
- `$exchangeRateService` (ExchangeRateServiceInterface) - The exchange rate service to use.
- `$locale` (?string) - The locale for currency formatting. `null` for auto-detection.
- `$autoRefreshTtl` (?int) - The maximum age of the rates in seconds before a currency conversion refetches them, or
  `null` to turn auto-refresh off (default).

**Throws:**
- [`FormatException`](https://github.com/mossy2100/PHP-Core/blob/main/docs/Exceptions/FormatException.md) - If the locale string is invalid.
- `DomainException` - If the auto-refresh TTL is negative.

---

## Unit data

### getUnits()

```php
public static function getUnits(): array
```

Get the currency unit data: the refreshed copy in the data directory if there is one, otherwise the bundled snapshot.
Never fetches anything.

**Returns:** `array` - The currency unit data, containing:
- `whenFetched` (string) - When the data was fetched, in UTC.
- `currencies` (array<string, string>) - Currency names mapped to their ISO 4217 codes.

### refreshUnits()

```php
public static function refreshUnits(): array
```

Fetch the ISO 4217 list from SIX Group now, write it to the data directory, and reload the currency units. Fund
currencies and a few special codes (e.g. `XTS`, `XXX`) are left out.

**Returns:** `array` - The currency unit data just written, in the same shape as `getUnits()`.

**Throws:**
- `RuntimeException` - If the ISO 4217 XML cannot be fetched or parsed, or a data file cannot be written.

### deleteUnits()

```php
public static function deleteUnits(): void
```

Delete the refreshed currency unit files (`CurrencyUnits.php` and `CurrencyUnits.xml`) from the data directory, so the
bundled snapshot is used again. Has no effect if the files don't exist.

---

## Exchange rate data

### getConversions()

```php
public static function getConversions(): ?array
```

Get the local exchange rates, as last fetched by `refreshRates()`. Never fetches anything.

**Returns:** `?array` - The currency conversion data, or `null` if there is none. It contains:
- `whenFetched` (string) - When the rates were fetched, in UTC.
- `serviceName` (string) - The name of the exchange rate service used.
- `definitions` (list<array{string, string, float}>) - Conversion definition tuples.

### refreshRates()

```php
public static function refreshRates(): array
```

Fetch the exchange rates from the configured service now, write them to the data directory, and reload the currency
conversions.

**Returns:** `array` - The conversion data just written, in the same shape as `getConversions()`.

**Throws:**
- `LogicException` - If the exchange rate service is not configured.
- `RuntimeException` - If the service fails to return valid data, or the data file cannot be written.

### getRatesFetchedAt()

```php
public static function getRatesFetchedAt(): ?DateTimeImmutable
```

Get when the local exchange rates were fetched. Useful for deciding when to call `refreshRates()` if auto-refresh is
off.

**Returns:** `?DateTimeImmutable` - The time the rates were fetched, or `null` if there are no local rates.

### deleteConversions()

```php
public static function deleteConversions(): void
```

Delete the exchange rate file (`CurrencyConversions.php`) from the data directory. Has no effect if the file doesn't
exist.

---

## Configuration

### getExchangeRateService()

```php
public static function getExchangeRateService(): ?ExchangeRateServiceInterface
```

**Returns:** `?ExchangeRateServiceInterface` - The exchange rate service, or `null` if not configured.

### setExchangeRateService()

```php
public static function setExchangeRateService(?ExchangeRateServiceInterface $exchangeRateService): void
```

Set the exchange rate service, or `null` to clear it. It's needed by `refreshRates()` and by auto-refresh. Usually set
via `init()`.

### getAutoRefreshTtl()

```php
public static function getAutoRefreshTtl(): ?int
```

**Returns:** `?int` - The maximum age of the rates in seconds, or `null` if auto-refresh is off.

### setAutoRefreshTtl()

```php
public static function setAutoRefreshTtl(?int $autoRefreshTtl): void
```

Set the auto-refresh TTL in seconds, or `null` to turn auto-refresh off. See [Auto-refresh](#auto-refresh).

**Throws:**
- `DomainException` - If the value is negative.

### getLocale()

```php
public static function getLocale(): ?string
```

Get the locale for currency formatting. Returns the explicitly set locale, or auto-detects from the HTTP
`Accept-Language` header, falling back to PHP's default locale. Once determined, the result is cached for subsequent
calls.

**Returns:** `?string` - The locale string, or `null` if none could be determined.

### setLocale()

```php
public static function setLocale(?string $locale): void
```

Set the locale used for currency formatting. Pass `null` to clear an explicitly set locale and revert to auto-detection.

**Throws:**
- [`FormatException`](https://github.com/mossy2100/PHP-Core/blob/main/docs/Exceptions/FormatException.md) - If the locale string is invalid.

### getDataDir()

```php
public static function getDataDir(): string
```

**Returns:** `string` - The data directory path.

### setDataDir()

```php
public static function setDataDir(string $dataDir): void
```

Set the directory where refreshed currency data files are stored. Creates the directory if it doesn't exist. Trailing
slashes are trimmed.

**Throws:**
- `DomainException` - If the path is empty.
- `RuntimeException` - If the directory cannot be created.

### resetDataDir()

```php
public static function resetDataDir(): void
```

Reset the data directory to the default, `mossy2100-quantities` in the system temp directory.

### getUnitsFilePath()

```php
public static function getUnitsFilePath(): string
```

**Returns:** `string` - The path of the refreshed currency units file.

### getConversionsFilePath()

```php
public static function getConversionsFilePath(): string
```

**Returns:** `string` - The path of the exchange rates file.

---

## Usage Examples

```php
use Mossy2100\Quantities\Currencies\CurrencyService;
use Mossy2100\Quantities\Currencies\ExchangeRateServices\OpenExchangeRatesService;

// Configure the service, and keep the data somewhere persistent.
CurrencyService::setDataDir('/var/cache/my-app/currencies');
CurrencyService::init(new OpenExchangeRatesService('your-api-key'));

// Fetch the rates now, e.g. from a cron job.
CurrencyService::refreshRates();

// Or let conversions refetch the rates when they're more than an hour old.
CurrencyService::setAutoRefreshTtl(3600);

// Check how old the rates are.
$fetchedAt = CurrencyService::getRatesFetchedAt();

// Occasionally, update the list of currencies.
CurrencyService::refreshUnits();
```

---

## See Also

- **[ExchangeRateServiceInterface](ExchangeRateServices/ExchangeRateServiceInterface.md)** - Interface for exchange rate providers.
- **[Money](../QuantityType/Money.md)** - Money quantity type.
- **[Currency Calculations](../../WorkingWithQuantities/CurrencyCalculations.md)** - Worked examples.
- **[UnitService](../Services/UnitService.md)** - Unit registry.
- **[ConversionService](../Services/ConversionService.md)** - Conversion registry.
- **[UnitSystem](../Enums/UnitSystem.md)** - Measurement system enum.
