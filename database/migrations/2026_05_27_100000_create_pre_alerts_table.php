<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('merchant_name');
            $table->string('order_number')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('carrier', 32);
            $table->date('expected_delivery_date')->nullable();
            $table->text('item_description');
            $table->decimal('declared_value', 12, 2)->nullable();
            $table->string('invoice_path')->nullable();
            $table->string('status', 32)->default('submitted');
            $table->text('admin_notes')->nullable();
            $table->text('customer_notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('tracking_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_alerts');
    }
};
