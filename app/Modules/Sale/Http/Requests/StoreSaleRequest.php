<?php

declare(strict_types=1);

namespace App\Modules\Sale\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reseller_id' => ['required', 'uuid', 'exists:resellers,id'],
            'customer_id' => ['nullable', 'uuid', 'exists:customers,id'],
            'representative_id' => ['nullable', 'uuid', 'exists:representatives,id'],
            'consignment_id' => ['nullable', 'uuid', 'exists:consignments,id'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.consignment_item_id' => ['nullable', 'uuid', 'exists:consignment_items,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('discount'))
        {
            $this->merge(['discount' => 0]);
        }
    }
}
