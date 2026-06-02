<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pre_alert_id')->nullable()->constrained()->nullOnDelete();
            $table->string('package_reference')->unique();
            $table->string('tracking_number')->nullable();
            $table->string('merchant_name')->nullable();
            $table->string('carrier', 32)->nullable();
            $table->decimal('weight_lbs', 8, 2)->nullable();
            $table->decimal('declared_value', 12, 2)->nullable();
            $table->decimal('amount_due', 12, 2)->default(0);
            $table->string('payment_status', 32)->default('unpaid');
            $table->string('payment_method', 32)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('status', 48)->default('awaiting_arrival');
            $table->timestamp('received_at_warehouse_at')->nullable();
            $table->timestamp('shipped_to_jamaica_at')->nullable();
            $table->timestamp('arrived_in_jamaica_at')->nullable();
            $table->timestamp('ready_for_pickup_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('customer_visible_notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
