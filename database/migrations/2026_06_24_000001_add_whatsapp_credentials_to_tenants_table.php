<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Meta Cloud API credentials stored per-tenant (encrypted).
            // NULL means the tenant has no WhatsApp API integration enabled;
            // those tenants fall back to the existing manual wa.me link.
            $table->text('whatsapp_api_token')->nullable()->after('whatsapp_number');
            $table->string('whatsapp_phone_number_id', 64)->nullable()->after('whatsapp_api_token');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_api_token', 'whatsapp_phone_number_id']);
        });
    }
};
