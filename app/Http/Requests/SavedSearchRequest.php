<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseApiRequest;
use App\Models\FacingDirection;
use App\Models\ListingSort;
use App\Models\ListingType;
use App\Models\PropertyOwnership;
use App\Models\PropertyType;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="SavedSearchRequest",
 *     type="object",
 *)
 */
class SavedSearchRequest extends BaseApiRequest
{
    /**
     * @OA\Property(property="title",type="string", example="Pak Eko")
     * @OA\Property(property="filterSet",ref="#/components/schemas/FilterSet")
     */

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'filterSet.city' => 'nullable|string',
            'filterSet.propertyType' => ['nullable', Rule::in(array_column(PropertyType::cases(), 'value'))],
            'filterSet.listingType' => ['nullable', Rule::in(array_column(ListingType::cases(), 'value'))],
            'filterSet.facing' => ['nullable', Rule::in(array_column(FacingDirection::cases(), 'value'))],
            'filterSet.ownership' => ['nullable', Rule::in(array_column(PropertyOwnership::cases(), 'value'))],
            'filterSet.price.min' => 'nullable|numeric',
            'filterSet.price.max' => 'nullable|numeric',
            'filterSet.lotSize.min' => 'nullable|numeric',
            'filterSet.lotSize.max' => 'nullable|numeric',
            'filterSet.buildingSize.min' => 'nullable|numeric',
            'filterSet.buildingSize.max' => 'nullable|numeric',
            'filterSet.bedroomCount.min' => 'nullable|numeric',
            'filterSet.bedroomCount.max' => 'nullable|numeric',
            'filterSet.bathroomCount.min' => 'nullable|numeric',
            'filterSet.bathroomCount.max' => 'nullable|numeric',
            'filterSet.carCount.min' => 'nullable|numeric',
            'filterSet.carCount.max' => 'nullable|numeric',
            'filterSet.floorCount' => 'nullable|numeric',
            'filterSet.electricPower' => 'nullable|numeric',
            'filterSet.sort' => ['nullable', Rule::in(array_column(ListingSort::cases(), 'value'))],
            'filterSet.order' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }
}
