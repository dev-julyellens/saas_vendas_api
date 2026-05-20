<?php

declare(strict_types=1);

namespace App\Modules\Consignment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reseller_id' => ['required', 'uuid', 'exists:resellers,id'],
            'representative_id' => ['nullable', 'uuid', 'exists:representatives,id'],
            'consigned_at' => ['required', 'date'],
            'expected_return_at' => ['nullable', 'date', 'after_or_equal:consigned_at'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
