<?php

namespace App\Http\Requests;

use App\Models\Enums\ClosingType;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ClosingRequest',
    type: 'object',
)]
class ClosingRequest extends BaseApiRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    #[OA\Property(property: 'closingType', ref: '#/components/schemas/ClosingType')]
    #[OA\Property(property: 'clientName', type: 'string')]
    #[OA\Property(property: 'clientPhoneNumber', type: 'string')]
    #[OA\Property(property: 'transactionValue', type: 'integer')]
    #[OA\Property(property: 'date', type: 'string', format: 'date')]
    public function rules()
    {
        return [
            'closingType' => ['required', Rule::enum(ClosingType::class)],
            'clientName' => 'required',
            'clientPhoneNumber' => 'required',
            'transactionValue' => 'required|numeric',
            'date' => 'required|date',
        ];
    }
}
