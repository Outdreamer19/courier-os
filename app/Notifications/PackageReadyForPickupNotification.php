<?php

namespace App\Notifications;

use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PackageReadyForPickupNotification extends Notification
{
    use Queueable;

    public function __construct(public Package $package) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reference = $this->package->package_reference;
        $currency = config('shipdjm.currency');
        $amount = number_format((float) $this->package->amount_due, 2);

        return (new MailMessage)
            ->subject("Package {$reference} is ready for pickup")
            ->greeting('Hello '.$notifiable->name.',')
            ->line("Great news — package **{$reference}** is ready for pickup in Jamaica.")
            ->line("Amount due: {$currency} \${$amount} (pay online when available, or in person at pickup).")
            ->line("Please bring a valid ID and your Ship'd JM customer reference.")
            ->action('View package details', url('/portal/packages/'.$this->package->id))
            ->line("Thank you for shipping with Ship'd JM.");
    }
}
