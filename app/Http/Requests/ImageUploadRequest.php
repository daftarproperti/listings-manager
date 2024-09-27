<?php

namespace App\Http\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ImageUploadRequest',
    type: 'object',
)]
class ImageUploadRequest extends BaseApiRequest
{
    #[OA\Property(property: 'image', type: 'string', format: 'binary')]

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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
