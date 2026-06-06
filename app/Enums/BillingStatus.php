<?php

namespace App\Enums;

enum BillingStatus: string
{
    case NotInvoiced = 'not_invoiced';
    case InvoiceCreated = 'invoice_created';
    case InvoiceSent = 'invoice_sent';
    case PaymentPending = 'payment_pending';
    case Paid = 'paid';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::NotInvoiced => 'Not invoiced',
            self::InvoiceCreated => 'Invoice created',
            self::InvoiceSent => 'Invoice sent',
            self::PaymentPending => 'Payment pending',
            self::Paid => 'Paid',
            self::Failed => 'Failed',
        };
    }
}
