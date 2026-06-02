<?php

namespace App\Enums;

enum Carrier: string
{
    case AmazonLogistics = 'amazon_logistics';
    case Usps = 'usps';
    case Ups = 'ups';
    case Fedex = 'fedex';
    case Dhl = 'dhl';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::AmazonLogistics => 'Amazon Logistics',
            self::Usps => 'USPS',
            self::Ups => 'UPS',
            self::Fedex => 'FedEx',
            self::Dhl => 'DHL',
            self::Other => 'Other',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
