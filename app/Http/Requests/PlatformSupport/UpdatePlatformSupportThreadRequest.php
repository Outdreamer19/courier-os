<?php

namespace App\Http\Requests\PlatformSupport;

use App\Models\PlatformSupportThread;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlatformSupportThreadRequest extends FormRequest
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
        return [
            'status' => [
                'required',
                'string',
                Rule::in([
                    PlatformSupportThread::STATUS_OPEN,
                    PlatformSupportThread::STATUS_CLOSED,
                ]),
            ],
        ];
    }
}
