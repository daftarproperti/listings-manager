<?php
namespace App\Http\Requests;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     schema="ListingRequest",
 *     type="object",
 *)
 */

class ListingRequest extends BaseApiRequest
{
    /**
     * @OA\Property(property="title",type="string", example="Rumah dijual di daerah pasteur")
     * @OA\Property(property="address",type="string", example="Jl. Pendidikan No. 1")
     * @OA\Property(property="description",type="string", example="Rumah bagus")
     * @OA\Property(property="price",type="integer", example=100000)
     * @OA\Property(property="lotSize",type="integer", example=1000)
     * @OA\Property(property="buildingSize",type="integer", example=2000)
     * @OA\Property(property="carCount",type="integer", example=4)
     * @OA\Property(property="bedroomCount",type="integer", example=3)
     * @OA\Property(property="bathroomCount",type="integer", example=2)
     * @OA\Property(property="floorCount",type="integer", example=2)
     * @OA\Property(property="electricPower",type="integer", example=2200)
     * @OA\Property(property="facing",type="string", example="Utara")
     * @OA\Property(property="ownership",type="string", example="SHM")
     * @OA\Property(property="city",type="string", example="Bandung")
     * @OA\Property(property="pictureUrls",type="array",
     *      @OA\Items(
     *          oneOf={
     *              @OA\Schema(type="string", format="binary", example="\x00\x00\x00\x04\x00\x00\x00\x04"),
     *              @OA\Schema(type="string", format="url", example="https://example.com/image.jpg"),
     *          },
     *  )
     * )
     * @OA\Property(property="coordinate",type="object",
     *      @OA\Property(property="latitude",type="integer"),
     *      @OA\Property(property="longitude",type="integer")
     * )
     * @OA\Property(property="isPrivate",type="boolean", example=false)
     */


    public function authorize()
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function rules()
    {
        return [
            'title' => 'required|string',
            'address' => 'nullable|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'lotSize' => 'required|numeric',
            'city' => 'required|string',
            'bedroomCount' => 'required|numeric',
            'bathroomCount' => 'required|numeric',
            'buildingSize' => 'required|numeric',
            'carCount' => 'nullable|numeric',
            'floorCount' => 'nullable|numeric',
            'electricPower' => 'nullable|numeric',
            'facing' => 'nullable|string',
            'ownership' => 'nullable|string',
            'pictureUrls' => 'nullable|array|max:10',
            'coordinate.latitude' => 'nullable|string',
            'coordinate.longitude' => 'nullable|string',
            'isPrivate' => 'required|boolean',
        ];
    }
}
