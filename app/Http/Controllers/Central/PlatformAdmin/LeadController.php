<?php

namespace App\Http\Controllers\Central\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Models\PlatformLead;
use App\Support\Leads\LeadForm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function index(): Response
    {
        $leads = PlatformLead::query()
            ->latest()
            ->limit(500)
            ->get()
            ->map(fn (PlatformLead $lead) => [
                'id' => $lead->id,
                'status' => $lead->status,
                'priority' => $lead->priority,
                'score' => $lead->score,
                'recommended_plan' => $lead->recommended_plan,
                'talking_points' => $lead->talking_points ?? [],
                'business_name' => $lead->business_name,
                'name' => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'preferred_channel' => $lead->preferred_channel,
                'challenge' => $lead->challenge,
                'admin_notes' => $lead->admin_notes,
                'answers' => [
                    'Stage' => LeadForm::label(LeadForm::STAGES, $lead->stage),
                    'Country' => LeadForm::label(LeadForm::COUNTRIES, $lead->country),
                    'Monthly volume' => LeadForm::label(LeadForm::VOLUMES, $lead->monthly_volume),
                    'US warehouse' => LeadForm::label(LeadForm::FORWARDERS, $lead->forwarder),
                    'Timeline' => LeadForm::label(LeadForm::TIMELINES, $lead->timeline),
                    'Interested in' => LeadForm::label(LeadForm::PLANS, $lead->plan_interest),
                    'Contact by' => LeadForm::label(LeadForm::CHANNELS, $lead->preferred_channel),
                    'Best time' => LeadForm::label(LeadForm::TIMES, $lead->best_time),
                ],
                'needs' => collect($lead->needs ?? [])
                    ->map(fn (string $key) => LeadForm::label(LeadForm::NEEDS, $key))
                    ->values(),
                'source' => $lead->utm_campaign ?: $lead->utm_source,
                'created_at' => $lead->created_at->toIso8601String(),
                'created_human' => $lead->created_at->diffForHumans(),
                'contacted_at' => $lead->contacted_at?->toIso8601String(),
            ]);

        return Inertia::render('central/admin/Leads', [
            'leads' => $leads,
            'statuses' => PlatformLead::STATUSES,
        ]);
    }

    public function update(Request $request, PlatformLead $lead): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(PlatformLead::STATUSES)],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($data['status'] !== PlatformLead::STATUS_NEW && $lead->contacted_at === null) {
            $data['contacted_at'] = now();
        }

        $lead->update($data);

        return back()->with('success', 'Lead updated.');
    }
}
