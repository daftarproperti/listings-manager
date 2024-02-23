<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Serializer\Serializer;

/**
 * Base class for types that auto casts using symfony serializer.
 *
 * Implementation just needs to extend this class and define $_castType to be its own type;
 *
 * @template TGet
 * @template TSet
 * @implements CastsAttributes<TGet, TSet>
 */
class BaseAttributeCaster implements CastsAttributes
{
    protected string $_castType;

    public function __construct()
    {
        if (!property_exists($this, '_castType')) {
            throw new \Exception("Type must be defined in subclasses of BaseAttributeCaster.");
        }
    }

    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        // Original value from mongodb is array, so cast it to object when accessing.
        $result = app(Serializer::class)->denormalize($value, $this->_castType);
        return $result;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof $this->_castType) {
            // If given already object, no need to do anything.
            return $value;
        }

        // If given as array, cast it to object.
        $result = app(Serializer::class)->denormalize($value, $this->_castType);
        return $result;
    }
}
