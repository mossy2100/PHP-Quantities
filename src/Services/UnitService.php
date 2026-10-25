<?php

declare(strict_types=1);

namespace Mossy2100\Quantities\Services;

use DomainException;
use InvalidArgumentException;
use Mossy2100\Quantities\Enums\UnitSystem;
use Mossy2100\Quantities\QuantityType;
use Mossy2100\Quantities\Units\Unit;
use Mossy2100\Quantities\Units\UnitTerm;

/**
 * Registry of units with lookup, filtering, and loading by system.
 */
class UnitService
{
    #region Private static properties

    /**
     * All known/supported units including defaults and custom.
     * Keys are unit names.
     *
     * @var ?array<string, Unit>
     */
    private static ?array $units = null;

    /**
     * Index of the registered units by symbol, including prefixed variants.
     * Keys are unit symbols.
     *
     * @var array<string, Unit>
     */
    private static array $unitsBySymbol = [];

    #endregion

    #region Lookup methods

    /**
     * Get a unit by its name, or null if not found.
     *
     * @param string $name The unit name (e.g. 'meter', 'kilogram').
     * @return ?Unit The matching unit, or null if not found.
     */
    public static function getByName(string $name): ?Unit
    {
        self::init();
        assert(self::$units !== null);
        return self::$units[$name] ?? null;
    }

    /**
     * Get the Unit object matching the given symbol, or null if not found.
     *
     * Supports ASCII, Unicode, and alternate symbols, with or without prefixes.
     * Since unit symbols are required to be unique, this method can only match zero or one units.
     *
     * @param string $symbol The unit symbol to search for.
     * @return ?Unit The matching unit, or null if not found.
     */
    public static function getBySymbol(string $symbol): ?Unit
    {
        self::init();
        return self::$unitsBySymbol[$symbol] ?? null;
    }

    /**
     * Get all units belonging to the given system of units.
     *
     * @param UnitSystem $system The system of units to match.
     * @return list<Unit> Units belonging to the given system.
     */
    public static function getBySystem(UnitSystem $system): array
    {
        self::init();
        assert(self::$units !== null);

        return array_values(array_filter(self::$units, static fn (Unit $unit) => $unit->belongsToSystem($system)));
    }

    /**
     * Get all units compatible with the given quantity type.
     *
     * @param QuantityType $quantityType The quantity type to match.
     * @return list<Unit> Units compatible with the given quantity type.
     */
    public static function getByQuantityType(QuantityType $quantityType): array
    {
        self::init();
        assert(self::$units !== null);

        return array_values(
            array_filter(self::$units, static fn (Unit $unit) => $unit->quantityType === $quantityType)
        );
    }

    /**
     * Get all known/supported units.
     *
     * @return array<string, Unit>
     */
    public static function getAll(): array
    {
        self::init();
        assert(self::$units !== null);

        return self::$units;
    }

    /**
     * Get all valid unit symbols, including base and prefixed variants.
     *
     * @return list<string> All valid symbols.
     */
    public static function getAllSymbols(): array
    {
        self::init();
        return array_map(strval(...), array_keys(self::$unitsBySymbol));
    }

    #endregion

    #region Registry methods

    /**
     * Add a unit to the system.
     *
     * @param Unit $unit The unit to add.
     * @param bool $replaceExisting Determines action to take if a unit with this name already exists in the registry.
     * If true, the existing unit will be replaced; otherwise, the operation will be terminated.
     * @return bool True if the unit was added, false if it already existed and was not replaced.
     * @throws DomainException If any of the new unit's symbols are equal to any existing unit's symbol.
     */
    public static function add(Unit $unit, bool $replaceExisting = false): bool
    {
        self::init();
        assert(self::$units !== null);

        // Check if we already have a unit with this name.
        if (isset(self::$units[$unit->name])) {
            // Skip if requested.
            if (!$replaceExisting) {
                return false;
            }

            // Replace the existing unit. Remove it first, so its symbols don't count as conflicts.
            self::remove($unit);
        }

        // Check if the new unit's symbols conflict with existing ones.
        $symbols = $unit->symbols;
        assert(is_array($symbols));
        foreach ($symbols as $symbol => $details) {
            if (isset(self::$unitsBySymbol[$symbol])) {
                throw new DomainException(
                    "Cannot add unit '$unit->name'. Its symbol '$symbol' is already used by another unit."
                );
            }
        }

        // All good, add the unit to the registry and the symbol index.
        self::$units[$unit->name] = $unit;
        foreach ($symbols as $symbol => $details) {
            self::$unitsBySymbol[$symbol] = $unit;
        }
        return true;
    }

    /**
     * Add a new unit from a unit definition.
     *
     * @param string $name The unit name.
     * @param array{
     *     asciiSymbol: string,
     *     dimension: string,
     *     systems?: list<UnitSystem>,
     *     prefixGroup?: int,
     *     unicodeSymbol?: string,
     *     alternateSymbol?: string
     * } $definition The unit definition.
     * @param bool $replaceExisting Determines action to take if a unit with this name already exists in the registry.
     * If true, the existing unit will be replaced; otherwise, the operation will be terminated.
     * @throws DomainException If any of the new unit's symbols are equal to any existing unit's symbol.
     * @throws InvalidArgumentException If the systems array contains non-UnitSystem values.
     */
    public static function addFromDefinition(string $name, array $definition, bool $replaceExisting = false): void
    {
        // Add the unit. If it's already in there, skip it.
        self::add(new Unit(
            $name,
            $definition['asciiSymbol'],
            $definition['dimension'],
            $definition['systems'] ?? [],
            $definition['prefixGroup'] ?? 0,
            $definition['unicodeSymbol'] ?? null,
            $definition['alternateSymbol'] ?? null
        ), $replaceExisting);
    }

    /**
     * Remove a unit from the system.
     *
     * @param string|Unit $unit The Unit object or the name of the unit to remove.
     */
    public static function remove(string|Unit $unit): void
    {
        // If the registry is not initialized yet, do nothing.
        if (self::$units === null) {
            return;
        }

        // Get the unit name. If it isn't registered, do nothing.
        $name = is_string($unit) ? $unit : $unit->name;
        $registeredUnit = self::$units[$name] ?? null;
        if ($registeredUnit === null) {
            return;
        }

        // Remove the unit from the registry and the symbol index.
        unset(self::$units[$name]);
        $symbols = $registeredUnit->symbols;
        assert(is_array($symbols));
        foreach ($symbols as $symbol => $details) {
            unset(self::$unitsBySymbol[$symbol]);
        }
        self::clearParseCaches();
    }

    /**
     * Remove all units from the registry.
     *
     * This will NOT trigger a re-initialization on next access.
     * The array would have to be manually rebuilt using init(), loadBySystem(), or add().
     */
    public static function removeAll(): void
    {
        self::$units = [];
        self::$unitsBySymbol = [];
        self::clearParseCaches();
    }

    /**
     * Reset the registry to its default initial state.
     *
     * It will be re-initialized on next access to the default systems of units.
     */
    public static function reset(): void
    {
        self::$units = null;
        self::$unitsBySymbol = [];
        self::clearParseCaches();
    }

    #endregion

    #region Loading methods

    /**
     * Load all units belonging to a specific system of units.
     *
     * @param UnitSystem $system The system of units to load units for.
     * @param bool $replaceExisting Determines action to take if any units are found with the same name in the registry.
     * If true, the existing unit will be replaced; otherwise, the operation will be terminated.
     * @throws DomainException If any of the new units' symbols are equal to any existing unit's symbol.
     */
    public static function loadSystem(UnitSystem $system, bool $replaceExisting = false): void
    {
        // Loop through all unit definitions and add any belonging to the specified system.
        foreach (self::getAllDefinitions() as $name => $definition) {
            // Only load units for the specified system.
            $unitSystems = $definition['systems'] ?? [];
            if (!in_array($system, $unitSystems, true)) {
                continue;
            }

            // Add the unit, replacing any existing one if requested.
            self::addFromDefinition($name, $definition, $replaceExisting);
        }
    }

    /**
     * Load all units.
     *
     * @param bool $replaceExisting Determines action to take if any units are found with the same name in the registry.
     * If true, the existing unit will be replaced; otherwise, the operation will be terminated.
     * @throws DomainException If any of the new units' symbols are equal to any existing unit's symbol.
     */
    public static function loadAll(bool $replaceExisting = false): void
    {
        // Loop through all unit definitions and add any belonging to the specified system.
        foreach (self::getAllDefinitions() as $name => $definition) {
            // Add the unit if not already there.
            self::addFromDefinition($name, $definition, $replaceExisting);
        }
    }

    #endregion

    #region Inspection methods

    /**
     * Check if a unit is in the registry.
     *
     * @param string|Unit $unit The unit or unit name to check.
     * @return bool True if the unit is in the registry.
     */
    public static function has(string|Unit $unit): bool
    {
        self::init();

        // Get the unit name.
        $name = is_string($unit) ? $unit : $unit->name;

        // Check if the unit is in the registry.
        return isset(self::$units[$name]);
    }

    /**
     * Get the number of units in the registry.
     *
     * @return int The number of registered units.
     */
    public static function count(): int
    {
        self::init();
        assert(self::$units !== null);
        return count(self::$units);
    }

    #endregion

    #region Helper methods

    /**
     * Clear the caches of parsed units and unit terms, so they can't return a unit that's no longer registered.
     */
    private static function clearParseCaches(): void
    {
        Unit::clearParseCache();
        UnitTerm::clearParseCache();
    }

    /**
     * Initialize the units array from the QuantityType classes and default UnitSystems.
     *
     * This is called lazily on first access.
     */
    private static function init(): void
    {
        if (self::$units === null) {
            self::removeAll();
            self::loadAll();
        }
    }

    /**
     * Gather all unit definitions from all QuantityType classes.
     *
     * @return array<string, array{
     *     asciiSymbol: string,
     *     unicodeSymbol?: string,
     *     prefixGroup?: int,
     *     alternateSymbol?: string,
     *     systems: list<UnitSystem>,
     *     dimension: string
     * }>
     */
    private static function getAllDefinitions(): array
    {
        $definitions = [];

        // Collect unit definitions.
        foreach (QuantityTypeService::getAll() as $qtyType) {
            foreach ($qtyType->unitDefinitions as $name => $definition) {
                $definitions[$name] = $definition;
            }
        }

        return $definitions;
    }

    #endregion
}
