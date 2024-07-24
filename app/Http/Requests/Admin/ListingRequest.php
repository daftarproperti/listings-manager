<?php

namespace App\Http\Requests\Admin;

use App\Models\Enums\ActiveStatus;
use App\Models\Enums\VerifyStatus;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ListingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'verifyStatus' => ['nullable', Rule::enum(VerifyStatus::class)],
            'activeStatus' => ['nullable', Rule::enum(ActiveStatus::class)],
            'coordinate.latitude' => 'nullable|numeric',
            'coordinate.longitude' => 'nullable|numeric',
        ];
    }
}
