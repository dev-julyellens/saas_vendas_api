<?php

declare(strict_types=1);

namespace App\Modules\Consignment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsignmentNotesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
