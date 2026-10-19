<?php

namespace App\Support\Leads;

/**
 * The answer choices for the three step contact form on courieros.co.
 *
 * One list feeds the Vue page (labels and hints), the request validation
 * (allowed keys) and the platform console (labels for each stored key), so
 * adding an option here is the only change needed.
 */
final class LeadForm
{
    public const STAGES = [
        'starting' => ['Starting a new courier business', 'Getting set up from scratch'],
        'manual' => ['Running on WhatsApp and spreadsheets', 'Customers message you and you track by hand'],
        'switching' => ['Using other courier software', 'Looking for something that fits better'],
        'scaling' => ['Established and ready to grow', 'More branches, more staff, more volume'],
    ];

    public const COUNTRIES = [
        'jamaica' => ['Jamaica', null],
        'trinidad' => ['Trinidad and Tobago', null],
        'barbados' => ['Barbados', null],
        'guyana' => ['Guyana', null],
        'bahamas' => ['The Bahamas', null],
        'cayman' => ['Cayman Islands', null],
        'other_caribbean' => ['Elsewhere in the Caribbean', null],
        'other' => ['Outside the Caribbean', null],
    ];

    public const VOLUMES = [
        'none' => ['Not shipping yet', null],
        'under_100' => ['Under 100', 'packages a month'],
        '100_500' => ['100 to 500', 'packages a month'],
        '500_2000' => ['500 to 2,000', 'packages a month'],
        'over_2000' => ['2,000 or more', 'packages a month'],
    ];

    public const FORWARDERS = [
        'own' => ['We run our own US warehouse', null],
        'partner' => ['We use a forwarding partner', null],
        'need' => ['We need help finding one', null],
        'unsure' => ['Not sure yet', null],
    ];

    public const NEEDS = [
        'website' => ['Branded website', 'Your name, your colours'],
        'portal' => ['Customer portal', 'Each customer gets a US address'],
        'prealerts' => ['Pre-alerts', 'Customers tell you what is coming'],
        'tracking' => ['Tracking and WhatsApp updates', 'No more "where is my package?"'],
        'payments' => ['Online payments', 'Invoices customers can pay by card'],
        'migration' => ['Move my existing customers', 'Bring your list over'],
        'custom_domain' => ['My own web address', 'Like myshipping.com'],
        'multi_location' => ['More than one location', 'Branches or warehouses'],
        'reporting' => ['Revenue reports', 'Know what each month made'],
    ];

    public const TIMELINES = [
        'now' => ['As soon as possible', 'This week if we can'],
        'month' => ['Within a month', null],
        'quarter' => ['In the next 1 to 3 months', null],
        'exploring' => ['Just exploring for now', null],
    ];

    public const PLANS = [
        'starter' => ['Starter', 'I am happy to set things up myself'],
        'done_for_you' => ['Done for you', 'Set it up and bring my customers over'],
        'custom' => ['A fully custom build', 'Something built just for my business'],
        'unsure' => ['Not sure', 'Tell me what makes sense'],
    ];

    public const CHANNELS = [
        'whatsapp' => ['WhatsApp', null],
        'call' => ['Phone call', null],
        'email' => ['Email', null],
    ];

    public const TIMES = [
        'morning' => ['Morning', '8am to 12pm'],
        'afternoon' => ['Afternoon', '12pm to 5pm'],
        'evening' => ['Evening', '5pm to 8pm'],
        'anytime' => ['Any time', null],
    ];

    /**
     * Every option group keyed by the name the request and Vue page use.
     *
     * @return array<string, array<string, array{0: string, 1: string|null}>>
     */
    public static function groups(): array
    {
        return [
            'stages' => self::STAGES,
            'countries' => self::COUNTRIES,
            'volumes' => self::VOLUMES,
            'forwarders' => self::FORWARDERS,
            'needs' => self::NEEDS,
            'timelines' => self::TIMELINES,
            'plans' => self::PLANS,
            'channels' => self::CHANNELS,
            'times' => self::TIMES,
        ];
    }

    /**
     * @return array<string, list<array{value: string, label: string, hint: string|null}>>
     */
    public static function forFrontend(): array
    {
        return collect(self::groups())
            ->map(fn (array $group) => collect($group)
                ->map(fn (array $option, string $value) => [
                    'value' => $value,
                    'label' => $option[0],
                    'hint' => $option[1],
                ])
                ->values()
                ->all())
            ->all();
    }

    /**
     * @param  array<string, array{0: string, 1: string|null}>  $group
     * @return list<string>
     */
    public static function keys(array $group): array
    {
        return array_keys($group);
    }

    /**
     * Human label for a stored key, for the platform console and emails.
     *
     * @param  array<string, array{0: string, 1: string|null}>  $group
     */
    public static function label(array $group, ?string $key): ?string
    {
        return $key === null ? null : ($group[$key][0] ?? $key);
    }
}
