<?php

namespace App\Support\Leads;

use App\Models\PlatformLead;

/**
 * Turns a contact form submission into the things Shane needs before he
 * picks up the phone: how urgent it is, which plan to lead with, and what
 * to raise on the first call.
 *
 * The score is a rough sort order, not a verdict. It favours people who are
 * ready soon, already have volume and already have a way to get packages
 * out of the US, because those are the ones who can go live fastest.
 */
final class LeadQualifier
{
    private const TIMELINE_POINTS = ['now' => 30, 'month' => 20, 'quarter' => 10, 'exploring' => 0];

    private const STAGE_POINTS = ['scaling' => 25, 'manual' => 20, 'switching' => 20, 'starting' => 10];

    private const VOLUME_POINTS = ['over_2000' => 25, '500_2000' => 20, '100_500' => 15, 'under_100' => 8, 'none' => 0];

    private const PLAN_POINTS = ['done_for_you' => 15, 'custom' => 15, 'starter' => 10, 'unsure' => 5];

    private const FORWARDER_POINTS = ['own' => 10, 'partner' => 10, 'unsure' => 3, 'need' => 0];

    public const HOT_THRESHOLD = 70;

    public const WARM_THRESHOLD = 40;

    /**
     * @param  array<string, mixed>  $lead  validated form data
     * @return array{score: int, priority: string, recommended_plan: string, talking_points: list<string>}
     */
    public function assess(array $lead): array
    {
        $score = (self::TIMELINE_POINTS[$lead['timeline']] ?? 0)
            + (self::STAGE_POINTS[$lead['stage']] ?? 0)
            + (self::VOLUME_POINTS[$lead['monthly_volume']] ?? 0)
            + (self::PLAN_POINTS[$lead['plan_interest']] ?? 0)
            + (self::FORWARDER_POINTS[$lead['forwarder']] ?? 0);

        $priority = match (true) {
            $score >= self::HOT_THRESHOLD => PlatformLead::PRIORITY_HOT,
            $score >= self::WARM_THRESHOLD => PlatformLead::PRIORITY_WARM,
            default => PlatformLead::PRIORITY_COLD,
        };

        return [
            'score' => $score,
            'priority' => $priority,
            'recommended_plan' => $this->recommendPlan($lead),
            'talking_points' => $this->talkingPoints($lead),
        ];
    }

    /**
     * @param  array<string, mixed>  $lead
     */
    private function recommendPlan(array $lead): string
    {
        $needs = (array) ($lead['needs'] ?? []);

        if ($lead['plan_interest'] === 'custom') {
            return 'custom';
        }

        if ($lead['plan_interest'] === 'done_for_you'
            || $lead['monthly_volume'] === 'over_2000'
            || in_array('multi_location', $needs, true)
            || in_array('custom_domain', $needs, true)
            || in_array('migration', $needs, true)) {
            return 'enterprise';
        }

        return 'starter';
    }

    /**
     * @param  array<string, mixed>  $lead
     * @return list<string>
     */
    private function talkingPoints(array $lead): array
    {
        $needs = (array) ($lead['needs'] ?? []);
        $points = [];

        $points[] = match ($lead['timeline']) {
            'now' => 'Ready to start now. Reply fast and offer a setup slot this week.',
            'month' => 'Wants to be live within a month. Agree a launch date on the first call.',
            'quarter' => 'Planning 1 to 3 months out. Book a demo and diarise a follow up.',
            default => 'Early research. Send the live demo and follow up in two weeks.',
        };

        $points[] = match ($lead['stage']) {
            'starting' => 'New business. Walk through what launch week looks like and the founding setup offer.',
            'manual' => 'Runs on WhatsApp and spreadsheets. Lead with pre-alerts and automatic status updates saving admin time.',
            'switching' => 'Switching from other software. Ask what they use today, what is missing, and whether they can export customers.',
            default => 'Established operator. Ask about staff, branches and how they handle volume today.',
        };

        if ($lead['forwarder'] === 'need') {
            $points[] = 'Has no US forwarding partner yet. Line up a warehouse introduction before the call.';
        } elseif ($lead['forwarder'] === 'unsure') {
            $points[] = 'Unsure how packages leave the US. Explain the forwarder options early.';
        }

        if (in_array('migration', $needs, true)) {
            $points[] = 'Wants existing customers moved over. Ask roughly how many and in what format.';
        }

        if (in_array('custom_domain', $needs, true)) {
            $points[] = 'Wants their own web address. Check whether they already own the domain.';
        }

        if (in_array('multi_location', $needs, true)) {
            $points[] = 'Needs more than one location. Confirm branch count and pickup points.';
        }

        if ($lead['plan_interest'] === 'custom') {
            $points[] = 'Asked about a fully custom build. Scope it and send a quote.';
        }

        if (in_array($lead['monthly_volume'], ['500_2000', 'over_2000'], true)) {
            $points[] = 'High volume. Confirm warehouse workflow and how many staff need accounts.';
        }

        if (! in_array($lead['country'], ['jamaica'], true)) {
            $points[] = 'Outside Jamaica. Confirm their currency and delivery areas.';
        }

        return $points;
    }
}
