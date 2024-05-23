<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseApiRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "TelegramUserProfileRequest",
    type: "object",
)]
class TelegramUserProfileRequest extends BaseApiRequest
{
    #[OA\Property(property: "name", type: "string", example: "Jono Doe")]
    #[OA\Property(property: "phoneNumber", type: "string", example: "081111111111")]
    #[OA\Property(property: "city", type: "string", example: "Surabaya")]
    #[OA\Property(property: "description", type: "string", example: "Agen terpercaya")]
    #[OA\Property(property: "company", type: "string", example: "Agen XXX")]
    #[OA\Property(property: "picture", type: "string", format: "binary", example: "\x00\x00\x00\x04\x00\x00\x00\x04")]
    #[OA\Property(property: "isPublicProfile", type: "boolean", example: "true")]

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
            'isPublicProfile' => 'nullable|boolean',
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
