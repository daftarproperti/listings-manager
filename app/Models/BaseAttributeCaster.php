<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Serializer\Serializer;

/**
 * Base class for types that auto casts using symfony serializer.
 *
 * Implementation just needs to extend this class.
 *
 * @implements CastsAttributes<static, static>
 */
class BaseAttributeCaster implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        // Original value from mongodb is array, so cast it to object when accessing.
        return static::fromArray($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof static) {
            // If given already object, no need to do anything.
            return $value;
        }

        return static::fromArray($value);
    }

    /**
     * Converts from array to this object using array denormalizer.
     */
    public static function fromArray(mixed $attributes): static
    {
        /** @var static $subclassObject */
        $subclassObject = app(Serializer::class)->denormalize($attributes, static::class);
        return $subclassObject;
    }
}
