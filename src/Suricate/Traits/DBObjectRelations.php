<?php

declare(strict_types=1);

namespace Suricate\Traits;

use Suricate\DBObject;

trait DBObjectRelations
{
    protected $relations = [];
    protected $relationValues = [];
    protected $loadedRelations = [];

    /**
     * Get relation from its name
     *
     * @param string $name
     */
    protected function getRelation($name)
    {
        if (
            isset($this->relationValues[$name]) &&
            $this->isRelationLoaded($name)
        ) {
            return $this->relationValues[$name];
        }

        if (!$this->isRelationLoaded($name)) {
            $this->loadRelation($name);
            $this->markRelationAsLoaded($name);
        }

        if (isset($this->relationValues[$name])) {
            return $this->relationValues[$name];
        }

        return null;
    }

    /**
     * Check if variable is predefined relation
     * @param  string  $name variable name
     * @return boolean
     */
    protected function isRelation($name)
    {
        return isset($this->relations[$name]);
    }
    /**
     * Define object relations
     *
     * @return DBObject
     */
    protected function setRelations()
    {
        $this->relations = [];

        return $this;
    }

    /**
     * Mark a relation as loaded
     * @param  string $name varialbe name
     * @return void
     */
    protected function markRelationAsLoaded($name)
    {
        if ($this->isRelation($name)) {
            $this->loadedRelations[$name] = true;
        }
    }
    /**
     * Check if a relation already have been loaded
     * @param  string  $name Variable name
     * @return boolean
     */
    protected function isRelationLoaded($name)
    {
        return isset($this->loadedRelations[$name]);
    }

    /**
     * Load realation according to relation type
     *
     * @param string $name
     * @return void
     */
    protected function loadRelation($name)
    {
        if ($this->isRelation($name)) {
            switch ($this->relations[$name]['type']) {
                case DBObject::RELATION_ONE_ONE:
                    $this->loadRelationOneOne($name);
                    return;
                case DBObject::RELATION_ONE_MANY:
                    $this->loadRelationOneMany($name);
                    return;
                case DBObject::RELATION_MANY_MANY:
                    $this->loadRelationManyMany($name);
                    return;
            }
        }
    }

    /**
     * Load one to one relationship
     *
     * @param string $name
     * @return void
     */
    private function loadRelationOneOne($name)
    {
        $target = $this->relations[$name]['target'];
        $source = $this->relations[$name]['source'];
        $this->relationValues[$name] = new $target();
        $this->relationValues[$name]->load($this->$source);
    }

    /**
     * Load one to many relationship
     *
     * @param string $name
     * @return void
     */
    private function loadRelationOneMany($name)
    {
        $target = $this->relations[$name]['target'];
        $parentId = $this->{$this->relations[$name]['source']};
        $parentIdField = isset($this->relations[$name]['target_field'])
            ? $this->relations[$name]['target_field']
            : null;
        $validate = dataGet($this->relations[$name], 'validate', null);

        $this->relationValues[$name] = $target::loadForParentId(
            $parentId,
            $parentIdField,
            $validate
        );
    }

    /**
     * Load many to many relationship
     *
     * @param string $name
     * @return void
     */
    private function loadRelationManyMany($name)
    {
        $pivot = $this->relations[$name]['pivot'];
        $sourceType = $this->relations[$name]['source_type'];
        $target = dataGet($this->relations[$name], 'target');
        $validate = dataGet($this->relations[$name], 'validate', null);

        $this->relationValues[$name] = $pivot::loadFor(
            $sourceType,
            $this->{$this->relations[$name]['source']},
            $target,
            $validate
        );
    }
}
