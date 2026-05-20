<?php

declare(strict_types=1);

namespace App\Modules\Reseller\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResellerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'representative_id' => ['sometimes', 'nullable', 'uuid', 'exists:representatives,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'document' => ['sometimes', 'nullable', 'string', 'max:20'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
