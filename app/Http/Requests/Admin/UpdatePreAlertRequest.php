<?php

namespace App\Http\Requests\Admin;

use App\Enums\PreAlertStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePreAlertRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(PreAlertStatus::class)],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
