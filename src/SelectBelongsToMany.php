<?php

namespace BbsLab\NovaSelectField;

use Closure;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use Laravel\Nova\Http\Controllers\ResourceAttachController;
use Laravel\Nova\Http\Requests\NovaRequest;

class SelectBelongsToMany extends BelongsToMany
{
    use Traits\BehaveHasBelongsTo;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-select-belongs-to-many-field';

    /**
     * @var \Closure|null
     */
    public $afterFillCallback;

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
        parent::__construct($name, $attribute, $resource);

        $resource = $resource ?? ResourceRelationshipGuesser::guessResource($name);

        $this->resourceClass = $resource;
        $this->resourceName = $resource::uriKey();
        $this->manyToManyRelationship = $this->attribute;
        $this->afterFillCallback = null;
    }

    /**
     * @param  \Closure  $afterFillCallback
     * @return $this
     */
    public function afterFillUsing(Closure $afterFillCallback)
    {
        $this->afterFillCallback = $afterFillCallback;

        return $this;
    }

    /**
     * Resolve the field's value.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
     * @return void
     */
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

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return mixed
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        if (! $request->exists($requestAttribute)) {
            return;
        }

        $value = $request[$requestAttribute];
        $value = (empty($value) || $value === 'null')
            ? []
            : explode(',', $value);

        $model->{$this->manyToManyRelationship}()->sync($value);

        if (is_callable($this->afterFillCallback)) {
            call_user_func($this->afterFillCallback, $model, $value);
        }
    }

    public function getRules(NovaRequest $request)
    {
        return $this->routeIsAttachable($request)
            ? parent::getRules($request)
            : Field::getRules($request);
    }

    protected function routeIsAttachable(NovaRequest $request): bool
    {
        return $request->route()->getController() instanceof ResourceAttachController;
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
