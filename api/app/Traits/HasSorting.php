<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use InvalidArgumentException;

trait HasSorting
{
    public static function prepareSortingQuery(Builder $query, $sortBy, $sortDirection): Builder
    {
        $sortBy = request()->get('sort-by');
        $direction = request()->get('sort-direction', 'asc');

        if (!$sortBy || $sortBy === 'null' || $sortBy === 'undefined') {
            return $query->orderBy('id', $direction);
        }

        try {
            if (self::isAggregateSort($sortBy)) {
                return self::handleAggregateSort($query, $sortBy, $sortDirection)
                    ->orderBy('id', $sortDirection);
            }

            if (self::isFarColumnSort($sortBy)) {
                return self::handleFarColumnSort($query, $sortBy, $sortDirection)
                    ->orderBy('id', $sortDirection);
            }

            if (self::isValidLocalColumn($query, $sortBy)) {
                return $query
                    ->orderBy($sortBy, $sortDirection)
                    ->orderBy('id', $sortDirection);
            }

            throw new InvalidArgumentException("Invalid sort column: {$sortBy}");
        } catch (\Exception $e) {
            \Log::error('Sorting error: ' . $e->getMessage());
            return $query; // Return unsorted query as fallback
        }
    }

    private function qualifyExistingWheresClauses(Builder $query, string $baseTable): void
    {
        $wheres = $query->getQuery()->wheres;

        foreach ($wheres as $key => $where) {
            if (isset($where['column']) && !str_contains($where['column'], '.')) {
                $query->getQuery()->wheres[$key]['column'] = "{$baseTable}.{$where['column']}";
            }
        }
    }

    private function isAggregateSort(string $sortBy): bool
    {
        return str_starts_with($sortBy, 'agg_count_') || str_starts_with($sortBy, 'agg_sum_');
    }

    private function isFarColumnSort(string $sortBy): bool
    {
        return str_contains($sortBy, '.') && !$this->isAggregateSort($sortBy);
    }

    private function isValidLocalColumn(Builder $query, string $column): bool
    {
        return \Schema::hasColumn($query->getModel()->getTable(), $column);
    }

    private function isValidRelationColumn($model, string $column): bool
    {
        return \Schema::hasColumn($model->getTable(), $column);
    }

    private function getJoinKeys($relationInstance): array
    {
        if ($relationInstance instanceof BelongsTo) {
            return [
                'foreign' => $relationInstance->getOwnerKeyName(),
                'local' => $relationInstance->getForeignKeyName()
            ];
        }

        if ($relationInstance instanceof HasMany || $relationInstance instanceof HasOne) {
            return [
                'foreign' => $relationInstance->getForeignKeyName(),
                'local' => $relationInstance->getLocalKeyName()
            ];
        }

        if ($relationInstance instanceof BelongsToMany) {
            return [
                'foreign' => $relationInstance->getRelatedPivotKeyName(),
                'local' => $relationInstance->getForeignPivotKeyName(),
                'pivot' => $relationInstance->getTable()
            ];
        }

        throw new InvalidArgumentException("Unsupported relation type: " . get_class($relationInstance));
    }

    private function handleFarColumnSort(Builder $query, string $sortBy, string $direction): Builder
    {
        $parts = explode('.', $sortBy);

        if (count($parts) !== 2) {
            throw new InvalidArgumentException("Invalid relation sort format: {$sortBy}");
        }

        [$relation, $column] = $parts;
        $relationMethod = Str::camel($relation);

        if (!method_exists($query->getModel(), $relationMethod)) {
            throw new InvalidArgumentException("Relation {$relationMethod} does not exist");
        }

        $relationInstance = $query->getModel()->$relationMethod();
        $baseTable = $query->getModel()->getTable();
        $relationTable = $relationInstance->getRelated()->getTable();

        $this->qualifyExistingWheresClauses($query, $baseTable);

        if (!$this->isValidRelationColumn($relationInstance->getRelated(), $column)) {
            throw new InvalidArgumentException("Invalid column {$column} for relation {$relationMethod}");
        }

        $joinKeys = $this->getJoinKeys($relationInstance);
        $relationKey = $joinKeys['foreign'];
        $baseKey = $joinKeys['local'];

        return $query
            ->leftJoin(
                $relationTable,
                "$relationTable.$relationKey",
                '=',
                "$baseTable.$baseKey"
            )
            ->select("$baseTable.*")
            ->orderBy("$relationTable.$column", $direction);
    }

    private function handleAggregateSort(Builder $query, string $sortBy, string $direction): Builder
    {
        preg_match('/agg_(count|sum)_(.+?)(?:\.(.+))?$/', $sortBy, $matches);

        if (count($matches) < 3) {
            throw new InvalidArgumentException("Invalid aggregate sort format: {$sortBy}");
        }

        $function = $matches[1];
        $relation = $matches[2];
        $column = $matches[3] ?? null;

        if ($function === 'sum' && !$column) {
            throw new InvalidArgumentException("Column must be specified for SUM aggregate");
        }

        $relationMethod = Str::camel($relation);

        if (!method_exists($query->getModel(), $relationMethod)) {
            throw new InvalidArgumentException("Relation {$relationMethod} does not exist");
        }

        $relationInstance = $query->getModel()->$relationMethod();
        $baseTable = $query->getModel()->getTable();
        $relationTable = $relationInstance->getRelated()->getTable();

        $this->qualifyExistingWheresClauses($query, $baseTable);

        $joinKeys = $this->getJoinKeys($relationInstance);

        $subQuery = $this->buildAggregateSubquery(
            $relationTable,
            $joinKeys,
            $baseTable,
            $function,
            $column
        );

        return $query->selectSub($subQuery, 'relation_aggregate')
            ->orderBy('relation_aggregate', $direction);
    }

    private function buildAggregateSubquery(
        string $relationTable,
        array $joinKeys,
        string $baseTable,
        string $function,
        string|null $column
    ): \Closure {
        return function ($query) use ($relationTable, $joinKeys, $baseTable, $function, $column) {
            $query->from($relationTable)
                ->selectRaw(
                    $function === 'count' && !$column
                    ? 'COUNT(*)'
                    : sprintf('%s(%s)', strtoupper($function), $column)
                )
                ->whereColumn(
                    "{$relationTable}.{$joinKeys['foreign']}",
                    "=",
                    "{$baseTable}.{$joinKeys['local']}"
                );
        };
    }
}
