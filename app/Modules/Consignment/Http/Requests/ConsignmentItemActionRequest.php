<?php

declare(strict_types=1);

namespace App\Modules\Consignment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsignmentItemActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'consignment_item_id' => ['required', 'uuid', 'exists:consignment_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
