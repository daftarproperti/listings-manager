<?php
namespace App\Http\Requests;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     schema="TelegramUserProfileRequest",
 *     type="object",
 * )
 */

class TelegramUserProfileRequest extends BaseApiRequest
{
    /**
     * @OA\Property(property="name",type="string", example="Jono Doe")
     * @OA\Property(property="phoneNumber",type="string", example="081111111111")
     * @OA\Property(property="city",type="string", example="Surabaya")
     * @OA\Property(property="description",type="string", example="Agen terpercaya")
     * @OA\Property(property="company",type="string", example="Agen XXX")
     * @OA\Property(property="picture",type="string", format="binary", example="\x00\x00\x00\x04\x00\x00\x00\x04")
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
            'name' => 'required|string',
            'phoneNumber' => 'nullable|string',
            'city' => 'nullable|string',
            'description' => 'nullable|string',
            'company' => 'nullable|string',
            'picture' => 'nullable',
        ];
    }
}
