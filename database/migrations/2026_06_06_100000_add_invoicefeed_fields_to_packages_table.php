<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('invoicefeed_invoice_id')->nullable()->after('amount_due');
            $table->string('invoicefeed_invoice_number')->nullable()->after('invoicefeed_invoice_id');
            $table->string('invoicefeed_invoice_url')->nullable()->after('invoicefeed_invoice_number');
            $table->string('invoicefeed_public_invoice_url')->nullable()->after('invoicefeed_invoice_url');
            $table->string('invoicefeed_payment_url')->nullable()->after('invoicefeed_public_invoice_url');
            $table->string('invoicefeed_status')->nullable()->after('invoicefeed_payment_url');
            $table->timestamp('invoicefeed_synced_at')->nullable()->after('invoicefeed_status');
            $table->string('billing_status')->default('not_invoiced')->after('invoicefeed_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'invoicefeed_invoice_id',
                'invoicefeed_invoice_number',
                'invoicefeed_invoice_url',
                'invoicefeed_public_invoice_url',
                'invoicefeed_payment_url',
                'invoicefeed_status',
                'invoicefeed_synced_at',
                'billing_status',
            ]);
        });
    }
};
