<?php

namespace App\Http\Requests;

use App\Rules\IndonesiaPhoneFormat;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserProfileRequest',
    type: 'object',
)]
class UserProfileRequest extends BaseApiRequest
{
    #[OA\Property(property: 'name', type: 'string', example: 'Jono Doe')]
    #[OA\Property(property: 'phoneNumber', type: 'string', example: '081111111111')]
    #[OA\Property(property: 'city', type: 'string', example: 'Surabaya')]
    #[OA\Property(property: 'cityId', type: 'integer', example: 123)]
    #[OA\Property(property: 'cityName', type: 'string', example: 'Surabaya')]
    #[OA\Property(property: 'description', type: 'string', example: 'Agen terpercaya')]
    #[OA\Property(property: 'company', type: 'string', example: 'Agen XXX')]
    #[OA\Property(property: 'picture', type: 'string', format: 'binary', example: "\x00\x00\x00\x04\x00\x00\x00\x04")]
    #[OA\Property(property: 'isPublicProfile', type: 'boolean', example: 'true')]
    #[OA\Property(property: 'delegatePhone', type: 'string', example: '081111111111')]

    public function authorize()
    {
        return true;
    }

    /**
     * Prepare inputs for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'isPublicProfile' => self::toBoolean($this->isPublicProfile),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required_if:setDelegate,null|string',
            'phoneNumber' => 'nullable|string',
            'city' => 'nullable|string',
            'cityId' => 'nullable|integer',
            'description' => 'nullable|string',
            'company' => 'nullable|string',
            'picture' => 'nullable',
            'isPublicProfile' => 'nullable|boolean',
            'delegatePhone' => ['nullable' , 'string' , new IndonesiaPhoneFormat()],
        ];
    }

    /**
     * Convert to bool
     *
     * @param mixed $booleable
     * @return boolean
     */
    private static function toBoolean($booleable)
    {
        return (bool) filter_var($booleable, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
