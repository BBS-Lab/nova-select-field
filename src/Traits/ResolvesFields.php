<?php

namespace BBSLab\NovaSelectField\Traits;

use BBSLab\NovaSelectField\SelectBelongsToMany;
use Laravel\Nova\Contracts\ListableField;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\ResourceToolElement;

trait ResolvesFields
{
    /**
     * Remove non-creation fields from the given collection.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Laravel\Nova\Fields\FieldCollection  $fields
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    protected function removeNonCreationFields(NovaRequest $request, FieldCollection $fields)
    {
        return $fields->reject(function ($field) use ($request) {
            return ($field instanceof ListableField &&
                ! $this->listableFieldAuthorizedInForm($field)) ||
                $field instanceof ResourceToolElement ||
                $field->attribute === 'ComputedField' ||
                ($field instanceof ID && $field->attribute === $this->resource->getKeyName()) ||
                ! $field->isShownOnCreation($request);
        });
    }

    /**
     * Remove non-update fields from the given collection.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Laravel\Nova\Fields\FieldCollection  $fields
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    protected function removeNonUpdateFields(NovaRequest $request, FieldCollection $fields)
    {
        return $fields->reject(function ($field) use ($request) {
            return ($field instanceof ListableField &&
                ! $this->listableFieldAuthorizedInForm($field)) ||
                $field instanceof ResourceToolElement ||
                $field->attribute === 'ComputedField' ||
                ($field instanceof ID && $field->attribute === $this->resource->getKeyName()) ||
                ! $field->isShownOnUpdate($request, $this->resource);
        });
    }

    protected function listableFieldAuthorizedInForm($field): bool
    {
        if ($field instanceof SelectBelongsToMany) {
            return  true;
        }

        return false;
    }
}
