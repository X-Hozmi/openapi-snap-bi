<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel query()
 * @method string getQualifiedDeletedAtColumn()
 *
 * @mixin \Eloquent
 */
abstract class BaseModel extends Model
{
    /**
     * Column mapping between onprem and cloud structure
     * Override this in child models
     * Format: ['standard_name' => ['onprem' => 'ONPREM_COLUMN', 'cloud' => 'cloud_column']]
     *
     * @var array<string, array<string, string|null>>
     */
    protected static array $columnMapping = [];

    /**
     * Tracks the last connection used when setting connection statically.
     */
    public static ?string $lastConnection = null;

    /**
     * Indicates if the model should use user stamps for non-cloud connections.
     * Override this in child models that have user stamps in non-cloud.
     *
     * @var bool
     */
    protected $hasUserStampsInNonCloud = false;

    /**
     * Indicates if the model should use timestamps for non-cloud connections.
     * Override this in child models that have timestamps in non-cloud.
     *
     * @var bool
     */
    protected $hasTimestampsInNonCloud = false;

    /**
     * Indicates if the model should use soft deletes for non-cloud connections.
     * Override this in child models that have soft deletes in non-cloud.
     *
     * @var bool
     */
    protected $hasSoftDeletesInNonCloud = false;

    /**
     * Get the current connection type
     */
    protected function getCurrentConnectionType(): string
    {
        return $this->getConnectionName() ?: Config::string('database.default');
    }

    /**
     * Check if current connection is cloud
     */
    public function isCloudConnection(): bool
    {
        return $this->getCurrentConnectionType() === 'cloud';
    }

    /**
     * Determine if the model should use user stamps for current connection.
     */
    public function shouldUseUserStamps(): bool
    {
        $hasUserStampsTrait = in_array('App\Traits\UserStamps', class_uses_recursive(static::class));

        if ($this->isCloudConnection()) {
            return $hasUserStampsTrait;
        }

        return $this->hasUserStampsInNonCloud && $hasUserStampsTrait;
    }

    /**
     * Determine if the model uses timestamps.
     */
    public function usesTimestamps(): bool
    {
        if ($this->isCloudConnection()) {
            return true;
        }

        return $this->hasTimestampsInNonCloud;
    }

    /**
     * Determine if the model should use soft deletes for current connection.
     */
    public function shouldUseSoftDeletes(): bool
    {
        $hasSoftDeletesTrait = in_array('App\Traits\ConditionalSoftDeletes', class_uses_recursive(static::class));

        if ($this->isCloudConnection()) {
            return $hasSoftDeletesTrait;
        }

        return $this->hasSoftDeletesInNonCloud && $hasSoftDeletesTrait && ! is_null($this->getActualColumnName('deleted_at'));
    }

    /**
     * Get the name of the "created at" column.
     * Override Laravel's default to use mapped column names.
     */
    public function getCreatedAtColumn(): string
    {
        /** @var string $createdAt */
        $createdAt = static::CREATED_AT ?? 'created_at';

        /** @var string $createdAtColumn */
        $createdAtColumn = $this->getActualColumnName($createdAt);

        return $createdAtColumn;
    }

    /**
     * Get the name of the "updated at" column.
     * Override Laravel's default to use mapped column names.
     */
    public function getUpdatedAtColumn(): string
    {
        /** @var string $updatedAt */
        $updatedAt = static::UPDATED_AT ?? 'updated_at';

        /** @var string $updatedAtColumn */
        $updatedAtColumn = $this->getActualColumnName($updatedAt);

        return $updatedAtColumn;
    }

    /**
     * Remove created_at from update data
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function removeCreatedAtForUpdate(array $data): array
    {
        /** @var string $createdAt */
        $createdAt = static::CREATED_AT ?? 'created_at';

        $actualCreatedAt = $this->getActualColumnName($createdAt);

        if (! is_null($actualCreatedAt) && array_key_exists($actualCreatedAt, $data)) {
            unset($data[$actualCreatedAt]); // TODO: test_should_remove_created_at_column_on_insert_or_update_mapped_method_if_data_exists_for_updating
        }

        return $data;
    }

    /**
     * Set the connection associated with the model.
     *
     * @param  string|null  $name
     * @return $this
     */
    public function setConnection($name)
    {
        static::$lastConnection = $name;

        return parent::setConnection($name);
    }

    /**
     * Get the actual column name based on current connection
     */
    public function getActualColumnName(string $standardName, ?string $connection = null): ?string
    {
        $connectionType = $connection ?? $this->getCurrentConnectionType() ?: static::$lastConnection ?? $this->getCurrentConnectionType();

        if (isset(static::$columnMapping[$standardName])) {
            $mapping = static::$columnMapping[$standardName];

            if (array_key_exists($connectionType, $mapping)) {
                return $mapping[$connectionType] ?? $standardName;
            }

            return null; // TODO: test_should_skip_non_existent_data_key_if_not_defined_in_connection_key_column_mapping
        }

        if (! $this->isCloudConnection()) {
            return match ($standardName) {
                'created_by' => $this->hasUserStampsInNonCloud ? 'created_by' : null,
                'updated_by' => $this->hasUserStampsInNonCloud ? 'updated_by' : null,
                'deleted_by' => ($this->hasUserStampsInNonCloud && $this->hasSoftDeletesInNonCloud) ? 'deleted_by' : null,

                'created_at' => $this->hasTimestampsInNonCloud ? 'created_at' : null,
                'updated_at' => $this->hasTimestampsInNonCloud ? 'updated_at' : null,

                'deleted_at' => $this->hasSoftDeletesInNonCloud ? 'deleted_at' : null,

                default => $standardName,
            };
        }

        return $standardName;
    }

    /**
     * Get multiple actual column names at once
     *
     * @param  list<string>  $standardNames
     * @return array<string|null>
     */
    public function getActualColumnNames(array $standardNames, ?string $connection = null): array
    {
        return array_map(function ($name) use ($connection) {
            return $this->getActualColumnName($name, $connection);
        }, $standardNames);
    }

    /**
     * Transform array of data using column mapping
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function transformDataForDatabase(array $data): array
    {
        $transformed = [];

        foreach ($data as $key => $value) {
            $actualColumn = $this->getActualColumnName($key);

            if (is_null($actualColumn)) {
                continue;
            }

            $transformed[$actualColumn] = $value;
        }

        return $transformed;
    }

    /**
     * Transform array of batch data for database insertion
     *
     * @param  list<array<string, mixed>>  $batchData
     * @return list<array<string, mixed>>
     */
    public function transformBatchDataForDatabase(array $batchData): array
    {
        return array_map(function ($data) {
            return $this->transformDataForDatabase($data);
        }, $batchData);
    }

    /**
     * Normalize data from actual column names (source) to standard names.
     *
     * @param  array<string, mixed>|list<array<string, mixed>>  $data
     * @param  list<string>|null  $uniqueBy
     * @return array{0: array<string, mixed>|list<array<string, mixed>>, 1: list<string>|null}
     *
     * TODO: test_normalize_record_method_should_be_able_to_normalize_data_from_actual_to_standard_names
     * TODO: test_normalize_record_method_should_also_normalize_unique_by_keys
     */
    public function normalizeRecord(array $data, ?array $uniqueBy = null, ?string $source = null): array
    {
        $connectionType = $source ?? $this->getCurrentConnectionType();

        $normalizedUniqueBy = null;
        if (! is_null($uniqueBy)) {
            $normalizedUniqueBy = $this->normalizeIdentifier($uniqueBy, $connectionType);
        }

        if (array_is_list($data)) {
            $normalizedList = array_map(function (mixed $row) use ($connectionType): array {
                /** @var array<string, mixed> $row */
                return $this->normalizeRecord($row, null, $connectionType)[0];
            }, $data);

            return [$normalizedList, $normalizedUniqueBy];
        }

        $normalized = [];

        $reverseMapping = [];
        foreach (static::$columnMapping as $standardName => $connections) {
            $actualColumn = $connections[$connectionType] ?? $standardName;
            $reverseMapping[$actualColumn] = $standardName;
        }

        foreach ($data as $key => $value) {
            $standardName = $reverseMapping[$key] ?? $key;
            $normalized[$standardName] = $value;
        }

        return [$normalized, $normalizedUniqueBy];
    }

    /**
     * Normalize identifiers (e.g., uniqueBy keys) from actual column names (source)
     * to standard names using the defined column mapping.
     *
     * @param  list<string>  $uniqueBy
     * @return list<string>
     *
     * TODO: test_normalize_identifier_method_should_be_able_to_normalize_identifier_keys
     */
    public function normalizeIdentifier(array $uniqueBy, ?string $source = null): array
    {
        $connectionType = $source ?? $this->getCurrentConnectionType();

        $reverseMapping = [];
        foreach (static::$columnMapping as $standardName => $connections) {
            $actualColumn = $connections[$connectionType] ?? $standardName;
            $reverseMapping[$actualColumn] = $standardName;
        }

        return array_map(function (string $column) use ($reverseMapping): string {
            return $reverseMapping[$column] ?? $column;
        }, $uniqueBy);
    }

    /**
     * Create a new query builder with optional table override
     *
     * @return Builder<self>
     */
    public function newQueryWithTable(?string $table = null): Builder
    {
        $query = $this->newQuery();

        if (! is_null($table)) {
            $query->from($table);
        }

        return $query;
    }

    /**
     * Enhanced insert method with column mapping and optional table
     *
     * @param  list<array<string, mixed>>  $values
     */
    public function insertMapped(array $values, ?string $table = null): bool
    {
        if (empty($values)) {
            return true;
        }

        $transformedValues = $this->transformBatchDataForDatabase($values);

        return $this->newQueryWithTable($table)->insert($transformedValues);
    }

    /**
     * Enhanced upsert method with column mapping and optional table
     *
     * @param  list<array<string, mixed>>  $values
     * @param  array<string>  $uniqueBy
     * @param  array<string>|null  $update
     */
    public function upsertMapped(array $values, array $uniqueBy, ?array $update = null, ?string $table = null): int
    {
        if (empty($values)) {
            return 0;
        }

        $transformedValues = $this->transformBatchDataForDatabase($values);

        $transformedUniqueBy = array_values(array_filter(array_map(function ($column) {
            return $this->getActualColumnName($column);
        }, $uniqueBy)));

        $transformedUpdate = null;
        if (! is_null($update)) {
            $transformedUpdate = array_values(array_filter(array_map(function ($column) {
                return $this->getActualColumnName($column);
            }, $update)));
        }

        if (empty($transformedUniqueBy)) {
            throw new InvalidArgumentException('No valid uniqueBy columns after mapping.');
        }

        return $this->newQueryWithTable($table)->upsert($transformedValues, $transformedUniqueBy, $transformedUpdate);
    }

    /**
     * Enhanced bulk updateOrInsert (from DB facades) method with column mapping
     *
     * @param  list<array<string, mixed>>  $values
     * @param  array<string>  $uniqueBy
     */
    public function updateOrInsertMapped(array $values, array $uniqueBy, ?string $table = null): int
    {
        if (empty($values)) {
            return 0; // @codeCoverageIgnore
        }

        /** @var string $connection */
        $connection = $this->getConnectionName() ?? Config::string('database.default');

        $tableName = $table ?? $this->getTable();

        $query = DB::connection($connection)->table($tableName);

        $transformedUniqueBy = [];
        foreach ($uniqueBy as $column) {
            $actualColumn = $this->getActualColumnName($column);

            if (is_null($actualColumn)) {
                throw new InvalidArgumentException(
                    "Column '{$column}' from uniqueBy is not available for connection '{$connection}'"
                );
            }

            $transformedUniqueBy[] = $actualColumn;
        }

        $affectedRows = 0;
        foreach ($values as $data) {
            $transformedValues = $this->transformDataForDatabase($data);

            $attributes = [];
            foreach ($transformedUniqueBy as $actualColumn) {
                if (! array_key_exists($actualColumn, $transformedValues)) {
                    throw new InvalidArgumentException(
                        "Unique column '{$actualColumn}' is missing in data"
                    );
                }
                $attributes[$actualColumn] = $transformedValues[$actualColumn];
            }

            $valuesToUpdate = array_diff_key(
                $transformedValues,
                $attributes
            );

            $query->updateOrInsert($attributes, $valuesToUpdate);

            $affectedRows++;
        }

        return $affectedRows;
    }

    /**
     * Enhanced insertOrIgnore method with column mapping and optional table
     *
     * @param  list<array<string, mixed>>  $values
     */
    public function insertOrIgnoreMapped(array $values, ?string $table = null): int
    {
        if (empty($values)) {
            return 0;
        }

        /** @var string $connection */
        $connection = $this->getConnectionName() ?? Config::string('database.default');

        $tableName = $table ?? $this->getTable();

        $transformedValues = $this->transformBatchDataForDatabase($values);

        return DB::connection($connection)->table($tableName)->insertOrIgnore($transformedValues);
    }

    /**
     * Create multiple records with mapping support and optional table
     *
     * @param  list<array<string, mixed>>  $records
     */
    public static function createMany(array $records, ?string $table = null): bool
    {
        if (empty($records)) {
            return true;
        }

        $instance = new static; // @phpstan-ignore-line

        return $instance->insertMapped($records, $table);
    }

    /**
     * Static method for upsert with mapping support and optional table
     *
     * @param  list<array<string, mixed>>  $values
     * @param  array<string>  $uniqueBy
     * @param  array<string>|null  $update
     */
    public static function upsertMany(array $values, array $uniqueBy, ?array $update = null, ?string $table = null): int
    {
        if (empty($values)) {
            return 0;
        }

        $instance = new static; // @phpstan-ignore-line

        return $instance->upsertMapped($values, $uniqueBy, $update, $table);
    }

    /**
     * Static method for insertOrIgnore with mapping support and optional table
     *
     * @param  list<array<string, mixed>>  $values
     */
    public static function insertOrIgnoreMany(array $values, ?string $table = null): int
    {
        if (empty($values)) {
            return 0;
        }

        $instance = new static; // @phpstan-ignore-line

        return $instance->insertOrIgnoreMapped($values, $table);
    }

    /**
     * Fluent interface for setting custom table
     */
    public function table(string $table): static
    {
        // Create a clone to avoid modifying the original instance
        $clone = clone $this;
        $clone->setTable($table);

        return $clone;
    }

    /**
     * Override setTable method to support dynamic table setting
     *
     * @param  string  $table
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get all column mappings for current connection
     *
     * @return array<string, string>
     */
    public function getColumnMappings(): array
    {
        $connectionType = $this->getCurrentConnectionType();
        $mappings = [];

        foreach (static::$columnMapping as $standardName => $connections) {
            $mappings[$standardName] = $connections[$connectionType] ?? $standardName;
        }

        return $mappings;
    }

    /**
     * Get the actual column name for a standard name
     * This is a static method to be used in queries
     */
    final public static function column(string $standardName): string
    {
        $instance = new static; // @phpstan-ignore-line
        $actual = $instance->getActualColumnName($standardName);

        if (is_null($actual)) {
            throw new InvalidArgumentException("Column mapping for '{$standardName}' is not available for current connection.");
        }

        return $actual;
    }

    /**
     * Override getAttribute to handle column mapping
     */
    public function getAttribute($key): mixed
    {
        $actualColumn = $this->getActualColumnName($key);

        if (is_null($actualColumn)) {
            return null;
        }

        return parent::getAttribute($actualColumn);
    }

    /**
     * Override setAttribute to handle column mapping
     */
    public function setAttribute($key, $value): mixed
    {
        $actualColumn = $this->getActualColumnName($key);

        if (is_null($actualColumn)) {
            return $this;
        }

        return parent::setAttribute($actualColumn, $value);
    }

    /**
     * Convert attributes array to use standard names
     *
     * @return array<string, mixed>
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();
        $connectionType = $this->getCurrentConnectionType();
        $convertedAttributes = [];

        // Create reverse mapping
        $reverseMapping = [];
        foreach (static::$columnMapping as $standardName => $connections) {
            $actualColumn = $connections[$connectionType] ?? $standardName;
            $reverseMapping[$actualColumn] = $standardName;
        }

        foreach ($attributes as $key => $value) {
            $standardName = $reverseMapping[$key] ?? $key;
            $convertedAttributes[$standardName] = $value;
        }

        return $convertedAttributes;
    }
}
