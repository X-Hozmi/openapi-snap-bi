<?php

namespace App\Traits;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * Conditional SoftDeletes Trait
 * Override Laravel's SoftDeletes trait to work with BaseModel's conditional logic
 */
trait ConditionalSoftDeletes
{
    use SoftDeletes {
        SoftDeletes::bootSoftDeletes as bootOriginalSoftDeletes;
    }

    /**
     * Boot the conditional soft deleting trait for the model.
     */
    public static function bootConditionalSoftDeletes(): void
    {
        // Don't boot the original SoftDeletes
        // Instead, add our custom conditional scope
        static::addGlobalScope(new class extends SoftDeletingScope
        {
            /**
             * Apply the scope to a given Eloquent query builder.
             *
             * @param  Builder<BaseModel>  $builder
             * @param  BaseModel  $model
             * @return void
             */
            public function apply($builder, $model)
            {
                assert($model instanceof BaseModel);
                assert(in_array(ConditionalSoftDeletes::class, class_uses_recursive($model)));

                // Only apply soft delete scope if the model should use soft deletes
                if ($model->shouldUseSoftDeletes()) {
                    // Get the actual deleted_at column name
                    $deletedAtColumn = $model->getActualColumnName('deleted_at');

                    // Only apply if the column exists in mapping
                    if (! is_null($deletedAtColumn)) {
                        /** @var string $qualifiedDeletedAtColumn */
                        $qualifiedDeletedAtColumn = $model->getQualifiedDeletedAtColumn();

                        $builder->whereNull($qualifiedDeletedAtColumn);
                    }
                }
            }
        });
    }

    /**
     * Override bootSoftDeletes to prevent double registration
     */
    public static function bootSoftDeletes(): void
    {
        // Do nothing - we handle this in bootConditionalSoftDeletes
    }

    /**
     * Force a hard delete on a soft deleted model.
     *
     * @return bool|null
     *
     * @codeCoverageIgnore
     */
    public function forceDelete()
    {
        if (! $this->shouldUseSoftDeletes()) {
            return $this->delete();
        }

        $this->forceDeleting = true;

        return tap($this->delete(), function ($deleted) {
            $this->forceDeleting = false;

            if ($deleted) {
                $this->fireModelEvent('forceDeleted', false);
            }
        });
    }

    /**
     * Perform the actual delete query on this model instance.
     */
    protected function performDeleteOnModel(): ?bool
    {
        if ($this->shouldUseSoftDeletes() && $this->forceDeleting !== true) {
            return $this->runSoftDelete();
        }

        parent::performDeleteOnModel();

        return null;
    }

    /**
     * Perform the actual delete query on this model instance.
     */
    protected function runSoftDelete(): bool
    {
        /** @var Builder<static> $newModelQuery */
        $newModelQuery = $this->newModelQuery();

        /** @var Builder<static> $query */
        $query = $this->setKeysForSaveQuery($newModelQuery);

        $time = $this->freshTimestamp();

        $deletedAtColumn = $this->getActualColumnName('deleted_at');

        if (is_null($deletedAtColumn)) {
            // If no deleted_at column mapping exists, perform hard delete
            parent::performDeleteOnModel();

            return true;
        }

        $columns = [$deletedAtColumn => $this->fromDateTime($time)];

        $this->{$deletedAtColumn} = $time;

        if ($this->usesTimestamps() && ! empty($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;
            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }

        return (bool) $query->update($columns);
    }

    /**
     * Restore a soft-deleted model instance.
     *
     * @return bool|null
     *
     * @codeCoverageIgnore
     */
    public function restore()
    {
        if (! $this->shouldUseSoftDeletes()) {
            return false;
        }

        $deletedAtColumn = $this->getActualColumnName('deleted_at');

        if (is_null($deletedAtColumn)) {
            return false;
        }

        // If the restoring event does not return false, we will proceed with this
        // restore operation. Otherwise, we bail out so the developer will stop
        // the restore totally. We will clear the deleted timestamp and save.
        if ($this->fireModelEvent('restoring') === false) {
            return false;
        }

        $this->{$deletedAtColumn} = null;

        // Once we have saved the model, we will fire the "restored" event so this
        // developer will do anything they need to after a restore operation is
        // totally finished. Then we will return the result of the save call.
        $this->exists = true;

        $result = $this->save();

        $this->fireModelEvent('restored', false);

        return $result;
    }

    /**
     * Determine if the model instance has been soft-deleted.
     *
     * @return bool
     *
     * @codeCoverageIgnore
     */
    public function trashed()
    {
        if (! $this->shouldUseSoftDeletes()) {
            return false;
        }

        $deletedAtColumn = $this->getActualColumnName('deleted_at');

        if (is_null($deletedAtColumn)) {
            return false;
        }

        return ! is_null($this->{$deletedAtColumn});
    }

    /**
     * Get the name of the "deleted at" column.
     * Override to handle conditional logic properly
     */
    public function getDeletedAtColumn(): string
    {
        if (! $this->shouldUseSoftDeletes()) {
            return 'deleted_at'; // Return default to avoid errors, but won't be used
        }

        $deletedAtColumn = $this->getActualColumnName('deleted_at');

        if (is_null($deletedAtColumn)) {
            return 'deleted_at'; // Return default to avoid errors
        }

        return $deletedAtColumn;
    }

    /**
     * Get the fully qualified "deleted at" column.
     *
     * @return string
     */
    public function getQualifiedDeletedAtColumn()
    {
        return $this->qualifyColumn($this->getDeletedAtColumn());
    }
}
