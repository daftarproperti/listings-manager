<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Data;

/**
 * Base class for types that auto casts using symfony serializer.
 *
 * Implementation just needs to extend this class.
 *
 * @implements CastsAttributes<static, static>
 */
class BaseAttributeCaster extends Data implements Castable, CastsAttributes
{
    // Castable impl
    // We need this because Spatie\LaravelData\Data also implements Castable and we don't want to use that.
    /**
     * @param array<mixed> $arguments
     */
    public static function castUsing(array $arguments): string
    {
        return static::class;
    }

    // CastAttributes impl
    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        // Original value from mongodb is array, so cast it to object when accessing.
        return static::from($value);
    }

    // CastAttributes impl
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof static) {
            // If given already object, no need to do anything.
            return $value;
        }

        return static::from($value);
    }
}
