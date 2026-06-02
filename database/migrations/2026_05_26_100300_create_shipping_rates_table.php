<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('method', 64)->default('standard');
            $table->string('currency', 8)->default('JMD');
            $table->decimal('rate_per_lb', 10, 2);
            $table->decimal('minimum_charge', 10, 2)->default(0);
            $table->decimal('handling_fee', 10, 2)->nullable();
            $table->decimal('min_weight_lbs', 8, 2)->nullable();
            $table->decimal('max_weight_lbs', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'min_weight_lbs', 'max_weight_lbs']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
