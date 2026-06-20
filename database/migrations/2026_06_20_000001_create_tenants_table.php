<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subdomain', 63)->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->string('status', 32)->default('pending');
            $table->string('currency', 8)->default('USD');
            $table->string('customer_reference_prefix', 16);
            $table->string('package_reference_prefix', 16)->default('PKG');
            $table->string('whatsapp_number', 32)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('brand_primary_color', 16)->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
