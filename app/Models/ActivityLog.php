<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'action',
    'subject_type',
    'subject_id',
    'description',
    'metadata',
])]
class ActivityLog extends Model
{
    public const ACTION_CUSTOMER_REGISTERED = 'customer.registered';

    public const ACTION_CUSTOMER_PROFILE_UPDATED = 'customer.profile_updated';

    public const ACTION_PRE_ALERT_CREATED = 'pre_alert.created';

    public const ACTION_PRE_ALERT_UPDATED = 'pre_alert.updated';

    public const ACTION_PRE_ALERT_CANCELLED = 'pre_alert.cancelled';

    public const ACTION_PRE_ALERT_STATUS_CHANGED = 'pre_alert.status_changed';

    public const ACTION_PACKAGE_CREATED = 'package.created';

    public const ACTION_PACKAGE_UPDATED = 'package.updated';

    public const ACTION_PACKAGE_STATUS_CHANGED = 'package.status_changed';

    public const ACTION_INVOICE_VIEWED = 'invoice.viewed';

    public const ACTION_CUSTOMER_UPDATED = 'customer.updated';

    public const ACTION_RECORD_DELETED = 'record.deleted';

    public const ACTION_ADMIN_USER_CREATED = 'admin_user.created';

    public const ACTION_ADMIN_USER_UPDATED = 'admin_user.updated';

    public const ACTION_BILLING_INVOICE_GENERATED = 'billing.invoice_generated';

    public const ACTION_BILLING_INVOICE_SENT = 'billing.invoice_sent';

    public const ACTION_BILLING_PAYMENT_LINK_CREATED = 'billing.payment_link_created';

    public const ACTION_BILLING_PAYMENT_SYNCED = 'billing.payment_synced';

    /**
     * @return array<string, string>
     */
    public static function actionLabels(): array
    {
        return [
            self::ACTION_CUSTOMER_REGISTERED => 'Customer registered',
            self::ACTION_CUSTOMER_PROFILE_UPDATED => 'Customer updated profile',
            self::ACTION_PRE_ALERT_CREATED => 'Pre-alert created',
            self::ACTION_PRE_ALERT_UPDATED => 'Pre-alert updated',
            self::ACTION_PRE_ALERT_CANCELLED => 'Pre-alert cancelled',
            self::ACTION_PRE_ALERT_STATUS_CHANGED => 'Pre-alert status changed',
            self::ACTION_PACKAGE_CREATED => 'Package created',
            self::ACTION_PACKAGE_UPDATED => 'Package updated',
            self::ACTION_PACKAGE_STATUS_CHANGED => 'Package status changed',
            self::ACTION_INVOICE_VIEWED => 'Invoice viewed',
            self::ACTION_CUSTOMER_UPDATED => 'Customer details updated',
            self::ACTION_RECORD_DELETED => 'Record deleted',
            self::ACTION_ADMIN_USER_CREATED => 'Admin user created',
            self::ACTION_ADMIN_USER_UPDATED => 'Admin user updated',
            self::ACTION_BILLING_INVOICE_GENERATED => 'Invoice generated',
            self::ACTION_BILLING_INVOICE_SENT => 'Invoice sent',
            self::ACTION_BILLING_PAYMENT_LINK_CREATED => 'Payment link created',
            self::ACTION_BILLING_PAYMENT_SYNCED => 'Payment status synced',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return self::actionLabels()[$this->action] ?? $this->action;
    }
}
