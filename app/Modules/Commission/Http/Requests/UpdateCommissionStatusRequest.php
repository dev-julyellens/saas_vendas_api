<?php

declare(strict_types=1);

namespace App\Modules\Commission\Http\Requests;

use App\Core\Enums\CommissionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommissionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(CommissionStatus::values())],
        ];
    }
}
