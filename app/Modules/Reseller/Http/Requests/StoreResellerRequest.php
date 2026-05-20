<?php

declare(strict_types=1);

namespace App\Modules\Reseller\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreResellerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'representative_id' => ['nullable', 'uuid', 'exists:representatives,id'],
            'name' => ['required', 'string', 'max:255'],
            'document' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['boolean'],
        ];
    }
}
