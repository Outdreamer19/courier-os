<?php

namespace App\Http\Requests\Admin;

use App\Models\ContactMessage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactMessageRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    ContactMessage::STATUS_NEW,
                    ContactMessage::STATUS_READ,
                    ContactMessage::STATUS_RESOLVED,
                ]),
            ],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
