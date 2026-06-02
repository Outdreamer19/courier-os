<?php

namespace App\Notifications;

use App\Enums\PackageStatus;
use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PackageStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Package $package,
        public ?PackageStatus $oldStatus,
        public PackageStatus $newStatus,
    ) {}

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

        $mail = (new MailMessage)
            ->subject("Package {$reference} status update")
            ->greeting('Hello '.$notifiable->name.',')
            ->line("Your package {$reference} status has been updated.")
            ->line('New status: '.$this->newStatus->label());

        if ($this->oldStatus) {
            $mail->line('Previous status: '.$this->oldStatus->label());
        }

        return $mail
            ->action('View package', url('/portal/packages/'.$this->package->id))
            ->line('Thank you for shipping with SHIP DJM.');
    }
}
