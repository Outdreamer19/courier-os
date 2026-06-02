<?php

namespace App\Support;

use App\Enums\PackageStatus;
use App\Models\Package;

class PackageStatusTimeline
{
    public static function apply(Package $package, PackageStatus $status): void
    {
        $now = now();

        match ($status) {
            PackageStatus::ReceivedAtFloridaWarehouse => $package->received_at_warehouse_at ??= $now,
            PackageStatus::InTransitToJamaica => $package->shipped_to_jamaica_at ??= $now,
            PackageStatus::ArrivedInJamaica => $package->arrived_in_jamaica_at ??= $now,
            PackageStatus::ReadyForPickup => $package->ready_for_pickup_at ??= $now,
            PackageStatus::PickedUp => $package->picked_up_at ??= $now,
            default => null,
        };
    }
}
