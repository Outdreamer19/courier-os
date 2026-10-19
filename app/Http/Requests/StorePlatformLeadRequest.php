<?php

namespace App\Http\Requests;

use App\Support\Leads\LeadForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlatformLeadRequest extends FormRequest
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
            // Step 1: the business
            'stage' => ['required', Rule::in(LeadForm::keys(LeadForm::STAGES))],
            'business_name' => ['nullable', 'string', 'max:160'],
            'country' => ['required', Rule::in(LeadForm::keys(LeadForm::COUNTRIES))],
            'monthly_volume' => ['required', Rule::in(LeadForm::keys(LeadForm::VOLUMES))],
            'forwarder' => ['required', Rule::in(LeadForm::keys(LeadForm::FORWARDERS))],

            // Step 2: what they need
            'needs' => ['required', 'array', 'min:1'],
            'needs.*' => ['string', 'distinct', Rule::in(LeadForm::keys(LeadForm::NEEDS))],
            'timeline' => ['required', Rule::in(LeadForm::keys(LeadForm::TIMELINES))],
            'plan_interest' => ['required', Rule::in(LeadForm::keys(LeadForm::PLANS))],
            'challenge' => ['nullable', 'string', 'max:2000'],

            // Step 3: how to reach them
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:160'],
            'preferred_channel' => ['required', Rule::in(LeadForm::keys(LeadForm::CHANNELS))],
            'phone' => [
                Rule::requiredIf(fn () => in_array($this->input('preferred_channel'), ['whatsapp', 'call'], true)),
                'nullable',
                'string',
                'max:32',
                'regex:/^[0-9+\s().-]{7,32}$/',
            ],
            'best_time' => ['required', Rule::in(LeadForm::keys(LeadForm::TIMES))],

            // Attribution, filled in by the page, never shown
            'utm_source' => ['nullable', 'string', 'max:120'],
            'utm_campaign' => ['nullable', 'string', 'max:160'],
            'referrer' => ['nullable', 'string', 'max:500'],

            // Honeypot: a field people never see. Bots fill it.
            'website' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'needs.required' => 'Pick at least one thing you need.',
            'needs.min' => 'Pick at least one thing you need.',
            'phone.required' => 'Add a number so we can reach you on WhatsApp or by phone.',
            'phone.regex' => 'That does not look like a phone number.',
        ];
    }
}
