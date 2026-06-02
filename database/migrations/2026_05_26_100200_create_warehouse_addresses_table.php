<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Florida Warehouse');
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city', 96);
            $table->string('state', 64);
            $table->string('zip', 24);
            $table->string('phone', 32)->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_addresses');
    }
};
