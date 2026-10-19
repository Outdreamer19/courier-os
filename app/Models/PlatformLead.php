<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * An enquiry from the CourierOS marketing contact page. Deliberately not
 * BelongsToTenant: the person on the other end is a prospective courier,
 * not anyone's customer yet.
 */
#[Fillable([
    'status',
    'priority',
    'score',
    'recommended_plan',
    'talking_points',
    'stage',
    'business_name',
    'country',
    'monthly_volume',
    'forwarder',
    'needs',
    'timeline',
    'plan_interest',
    'challenge',
    'name',
    'email',
    'phone',
    'preferred_channel',
    'best_time',
    'utm_source',
    'utm_campaign',
    'referrer',
    'admin_notes',
    'contacted_at',
])]
class PlatformLead extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_QUALIFIED = 'qualified';

    public const STATUS_WON = 'won';

    public const STATUS_LOST = 'lost';

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_CONTACTED,
        self::STATUS_QUALIFIED,
        self::STATUS_WON,
        self::STATUS_LOST,
    ];

    public const PRIORITY_HOT = 'hot';

    public const PRIORITY_WARM = 'warm';

    public const PRIORITY_COLD = 'cold';

    protected function casts(): array
    {
        return [
            'needs' => 'array',
            'talking_points' => 'array',
            'score' => 'integer',
            'contacted_at' => 'datetime',
        ];
    }
}
