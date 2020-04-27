<?php

namespace BbsLab\NovaSelectField;

use Illuminate\Support\Str;
use Laravel\Nova\Contracts\RelatableField;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FormatsRelatableDisplayValues;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use Laravel\Nova\Http\Requests\NovaRequest;

class SelectBelongsToMany extends Field implements RelatableField
{
    use Traits\BehaveHasBelongsTo, FormatsRelatableDisplayValues;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-select-belongs-to-many-field';

    /**
     * The class name of the related resource.
     *
     * @var string
     */
    public $resourceClass;

    /**
     * The URI key of the related resource.
     *
     * @var string
     */
    public $resourceName;

    /**
     * The name of the Eloquent "belongs to many" relationship.
     *
     * @var string
     */
    public $manyToManyRelationship;

    /**
     * The column that should be displayed for the field.
     *
     * @var \Closure
     */
    public $display;

    /**
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|null  $attribute
     * @param  string|null  $resource
     * @return void
     */
    public function __construct($name, $attribute = null, $resource = null)
    {
        parent::__construct($name, $attribute);

        $resource = $resource ?? ResourceRelationshipGuesser::guessResource($name);

        $this->resourceClass = $resource;
        $this->resourceName = $resource::uriKey();
        $this->manyToManyRelationship = $this->attribute;
    }

    public function resolve($resource, $attribute = null)
    {
        $attribute = $attribute ?? $this->manyToManyRelationship;

        /** @var \Illuminate\Support\Collection $objects */
        $objects = $resource->{$attribute};

        if (! $objects || $objects->isEmpty()) {
            return;
        }

        $this->value = $objects->mapInto($this->resourceClass)
            ->map(function ($resource) {
                return [
                    'display' => $this->formatDisplayValue($resource),
                    'value' => $resource->getKey(),
                ];
            })->sortBy('display', SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        if (! $request->exists($requestAttribute)) {
            return;
        }

        $value = $request[$requestAttribute];

        if (empty($value)) {
            return;
        }

        $value = explode(',', $value);

        $model->{$this->manyToManyRelationship}()->sync($value);
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge([
            'belongsToManyRelationship' => $this->manyToManyRelationship,
            'listable' => true,
            'perPage'=> $this->resourceClass::$perPageViaRelationship,
            'validationKey' => $this->validationKey(),
            'resourceName' => $this->resourceName,
            'singularLabel' => $this->singularLabel ?? Str::singular($this->name),
            'selectedResources' => $this->value,
        ], parent::jsonSerialize());
    }
}
