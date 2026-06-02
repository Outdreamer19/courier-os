<?php

namespace App\Enums;

enum PackageStatus: string
{
    case AwaitingArrival = 'awaiting_arrival';
    case ReceivedAtFloridaWarehouse = 'received_at_florida_warehouse';
    case Processing = 'processing';
    case InTransitToJamaica = 'in_transit_to_jamaica';
    case ArrivedInJamaica = 'arrived_in_jamaica';
    case CustomsProcessing = 'customs_processing';
    case ReadyForPickup = 'ready_for_pickup';
    case PickedUp = 'picked_up';
    case OnHold = 'on_hold';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::AwaitingArrival => 'Awaiting Arrival',
            self::ReceivedAtFloridaWarehouse => 'Received at Florida Warehouse',
            self::Processing => 'Processing',
            self::InTransitToJamaica => 'In Transit to Jamaica',
            self::ArrivedInJamaica => 'Arrived in Jamaica',
            self::CustomsProcessing => 'Customs Processing',
            self::ReadyForPickup => 'Ready for Pickup',
            self::PickedUp => 'Picked Up',
            self::OnHold => 'On Hold',
            self::Cancelled => 'Cancelled',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::PickedUp, self::Cancelled], true);
    }

    public function isActive(): bool
    {
        return ! $this->isTerminal();
    }
}
