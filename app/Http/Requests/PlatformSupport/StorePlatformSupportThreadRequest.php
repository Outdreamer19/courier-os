<?php

namespace App\Http\Requests\PlatformSupport;

use Illuminate\Foundation\Http\FormRequest;

class StorePlatformSupportThreadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'subject' => ['required', 'string', 'min:3', 'max:160'],
            'body' => ['required', 'string', 'min:1', 'max:10000'],
        ];

        if ($this->user()?->isPlatformOwner()) {
            $rules['tenant_id'] = ['required', 'integer', 'exists:tenants,id'];
        }

        return $rules;
    }
}
