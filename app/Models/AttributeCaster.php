<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Caster for Spatie\LaravelData\Data.
 *
 * @implements CastsAttributes<static, static>
 */
class AttributeCaster implements CastsAttributes
{
    // The class of the data must have ::from() method, i.e. extends Spatie\LaravelData\Data.
    private string $dataClass;

    public function __construct(string $dataClass) {
        $this->dataClass = $dataClass;
    }

    // CastAttributes impl
    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        // Original value from mongodb is array, so cast it to object when accessing.
        return $this->dataClass::from($value);
    }

    // CastAttributes impl
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof static) {
            // If given already object, no need to do anything.
            return $value;
        }

        return $this->dataClass::from($value);
    }
}
