<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enquiries from the central marketing site's contact page. Not tenant
     * scoped: these are prospective couriers talking to CourierOS itself,
     * kept apart from contact_messages, which belong to a tenant's own site.
     */
    public function up(): void
    {
        Schema::create('platform_leads', function (Blueprint $table) {
            $table->id();
            $table->string('status', 20)->default('new');
            $table->string('priority', 10)->default('cold');
            $table->unsignedSmallInteger('score')->default(0);
            $table->string('recommended_plan', 20)->nullable();
            $table->json('talking_points')->nullable();

            // Step 1: the business
            $table->string('stage', 20);
            $table->string('business_name', 160)->nullable();
            $table->string('country', 30);
            $table->string('monthly_volume', 20);
            $table->string('forwarder', 20);

            // Step 2: what they need
            $table->json('needs');
            $table->string('timeline', 20);
            $table->string('plan_interest', 20);
            $table->text('challenge')->nullable();

            // Step 3: how to reach them
            $table->string('name', 120);
            $table->string('email', 160);
            $table->string('phone', 32)->nullable();
            $table->string('preferred_channel', 20);
            $table->string('best_time', 20);

            // Attribution
            $table->string('utm_source', 120)->nullable();
            $table->string('utm_campaign', 160)->nullable();
            $table->string('referrer', 500)->nullable();

            $table->text('admin_notes')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['priority', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_leads');
    }
};
