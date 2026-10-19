<?php

namespace App\Notifications;

use App\Models\PlatformLead;
use App\Support\Leads\LeadForm;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlatformLeadReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PlatformLead $lead) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lead = $this->lead;
        $who = $lead->business_name ? "{$lead->name} ({$lead->business_name})" : $lead->name;
        $priority = strtoupper($lead->priority);

        $needs = collect($lead->needs ?? [])
            ->map(fn (string $key) => LeadForm::label(LeadForm::NEEDS, $key))
            ->implode(', ');

        $mail = (new MailMessage)
            ->subject("[{$priority}] New CourierOS lead: {$who}")
            ->greeting('Hello '.$notifiable->name.',')
            ->line("{$who} sent a brief from the contact page. Score {$lead->score}, recommended plan: ".ucfirst((string) $lead->recommended_plan).'.')
            ->line('Stage: '.LeadForm::label(LeadForm::STAGES, $lead->stage))
            ->line('Country: '.LeadForm::label(LeadForm::COUNTRIES, $lead->country))
            ->line('Volume: '.LeadForm::label(LeadForm::VOLUMES, $lead->monthly_volume))
            ->line('US warehouse: '.LeadForm::label(LeadForm::FORWARDERS, $lead->forwarder))
            ->line('Needs: '.$needs)
            ->line('Timeline: '.LeadForm::label(LeadForm::TIMELINES, $lead->timeline))
            ->line('Interested in: '.LeadForm::label(LeadForm::PLANS, $lead->plan_interest))
            ->line('Reach them by '.LeadForm::label(LeadForm::CHANNELS, $lead->preferred_channel)
                .', '.strtolower((string) LeadForm::label(LeadForm::TIMES, $lead->best_time))
                .': '.($lead->phone ?: $lead->email));

        if ($lead->challenge) {
            $mail->line('Biggest headache: "'.str($lead->challenge)->limit(300).'"');
        }

        foreach ($lead->talking_points ?? [] as $point) {
            $mail->line('• '.$point);
        }

        return $mail->action('Open in the platform console', url('/platform/leads?lead='.$lead->id));
    }
}
