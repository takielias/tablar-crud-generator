<?php

namespace Tablar\CrudGenerator;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Class ModelGenerator.
 */
class ModelGenerator
{
    private $functions = null;

    private $table = null;
    private $properties = null;
    private $modelNamespace = 'App';

    /**
     * ModelGenerator constructor.
     *
     * @param string $table
     * @param string $properties
     * @param string $modelNamespace
     */
    public function __construct(string $table, string $properties, string $modelNamespace)
    {
        $this->table = $table;
        $this->properties = $properties;
        $this->modelNamespace = $modelNamespace;
        $this->_init();
    }

    /**
     * Get all the eloquent relations.
     *
     * @return array
     */
    public function getEloquentRelations()
    {
        return [$this->functions, $this->properties];
    }

    private function _init()
    {
        foreach ($this->_getTableRelations() as $relation) {
            if ($relation->ref) {
                $tableKeys = $this->_getTableKeys($relation->ref_table);
                $eloquent = $this->_getEloquent($relation, $tableKeys);
            } else {
                $eloquent = 'hasOne';
            }

            $this->functions .= $this->_getFunction($eloquent, $relation->ref_table, $relation->foreign_key, $relation->local_key);
        }
    }

    /**
     * @param $relation
     * @param $tableKeys
     *
     * @return string
     */
    private function _getEloquent($relation, $tableKeys)
    {
        $eloquent = '';
        foreach ($tableKeys as $tableKey) {
            $columns = $tableKey['columns'] ?? [];

            if (! in_array($relation->foreign_key, $columns, true)) {
                continue;
            }

            $eloquent = 'hasMany';

            if (! empty($tableKey['primary'])) {
                $eloquent = 'hasOne';
            } elseif (! empty($tableKey['unique']) && (reset($columns) === $relation->foreign_key)) {
                $eloquent = 'hasOne';
            }
        }

        return $eloquent;
    }

    /**
     * @param string $relation
     * @param string $table
     * @param string $foreign_key
     * @param string $local_key
     *
     * @return string
     */
    private function _getFunction(string $relation, string $table, string $foreign_key, string $local_key)
    {
        list($model, $relationName) = $this->_getModelName($table, $relation);
        $relClass = ucfirst($relation);

        switch ($relation) {
            case 'hasOne':
                $this->properties .= "\n * @property $model $$relationName";
                break;
            case 'hasMany':
                $this->properties .= "\n * @property ".$model."[] $$relationName";
                break;
        }

        return '
    /**
     * @return \Illuminate\Database\Eloquent\Relations\\'.$relClass.'
     */
    public function '.$relationName.'()
    {
        return $this->'.$relation.'(\''.$this->modelNamespace.'\\'.$model.'\', \''.$foreign_key.'\', \''.$local_key.'\');
    }
    ';
    }

    /**
     * Get the name relation and model.
     *
     * @param $name
     * @param $relation
     *
     * @return array
     */
    private function _getModelName($name, $relation)
    {
        $class = Str::studly(Str::singular($name));
        $relationName = '';

        switch ($relation) {
            case 'hasOne':
                $relationName = Str::camel(Str::singular($name));
                break;
            case 'hasMany':
                $relationName = Str::camel(Str::plural($name));
                break;
        }

        return [$class, $relationName];
    }

    /**
     * Get all relations from Table.
     *
     * Driver-agnostic via Laravel Schema (Laravel 10.32+). Iterates every other
     * table to find inbound foreign keys (other -> this) and reads this table's
     * outbound foreign keys directly. Works on sqlite / mysql / pgsql / sqlsrv.
     *
     * @return array
     */
    private function _getTableRelations()
    {
        $relations = [];
        $thisTable = $this->table;

        // Inbound: foreign keys on OTHER tables that reference this table.
        foreach (Schema::getTables() as $other) {
            $otherName = $other['name'] ?? null;

            if (! $otherName || $otherName === $thisTable) {
                continue;
            }

            foreach (Schema::getForeignKeys($otherName) as $fk) {
                if (($fk['foreign_table'] ?? null) !== $thisTable) {
                    continue;
                }

                $columns = $fk['columns'] ?? [];
                $foreignColumns = $fk['foreign_columns'] ?? [];

                $relations[] = (object) [
                    'ref_table' => $otherName,
                    'foreign_key' => $columns[0] ?? '',
                    'local_key' => $foreignColumns[0] ?? '',
                    'ref' => '1',
                ];
            }
        }

        // Outbound: foreign keys on THIS table that reference others.
        foreach (Schema::getForeignKeys($thisTable) as $fk) {
            $columns = $fk['columns'] ?? [];
            $foreignColumns = $fk['foreign_columns'] ?? [];

            $relations[] = (object) [
                'ref_table' => $fk['foreign_table'] ?? '',
                'foreign_key' => $foreignColumns[0] ?? '',
                'local_key' => $columns[0] ?? '',
                'ref' => '0',
            ];
        }

        usort($relations, fn ($a, $b) => strcmp($a->ref_table, $b->ref_table));

        return $relations;
    }

    /**
     * Get all indexes from a table.
     *
     * Driver-agnostic via Schema::getIndexes (Laravel 10.32+). Each entry is
     * an array shape: [name, columns, type, unique, primary].
     *
     * @param  string  $table
     * @return array
     */
    private function _getTableKeys($table)
    {
        return Schema::getIndexes($table);
    }
}
