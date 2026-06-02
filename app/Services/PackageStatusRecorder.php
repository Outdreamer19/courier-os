<?php

namespace App\Services;

use App\Enums\PackageStatus;
use App\Models\Package;
use App\Models\PackageStatusHistory;
use App\Models\User;
use App\Notifications\PackageReadyForPickupNotification;
use App\Notifications\PackageStatusChangedNotification;

class PackageStatusRecorder
{
    public function record(
        Package $package,
        ?PackageStatus $oldStatus,
        PackageStatus $newStatus,
        ?User $changedBy = null,
        ?string $notes = null,
        bool $notifyCustomer = true,
    ): void {
        if ($oldStatus === $newStatus) {
            return;
        }

        PackageStatusHistory::create([
            'package_id' => $package->id,
            'old_status' => $oldStatus?->value,
            'new_status' => $newStatus->value,
            'changed_by' => $changedBy?->id,
            'notes' => $notes,
        ]);

        if (! $notifyCustomer) {
            return;
        }

        $customer = $package->user;

        if (! $customer) {
            return;
        }

        $customer->notify(new PackageStatusChangedNotification($package, $oldStatus, $newStatus));

        if ($newStatus === PackageStatus::ReadyForPickup) {
            $customer->notify(new PackageReadyForPickupNotification($package));
        }
    }
}
