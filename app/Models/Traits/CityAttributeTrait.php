<?php
namespace App\Models\Traits;

use App\Models\City;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @template ModelT
 * @property City|null $administrativeCity
 * @property string|null $cityName
 */
trait CityAttributeTrait {


     /**
     * Retrieve the administrative city relationship for this model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<City, ModelT> The administrative city relationship.
     */
    public function administrativeCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'cityId', 'osmId');
    }

    /**
     * Retrieve the name of the administrative city.
     *
     * @return string|null The name of the administrative city, or null if it does not exist.
     */
    public function getCityNameAttribute(): ?string
    {
        return $this->administrativeCity?->name;
    }

}
