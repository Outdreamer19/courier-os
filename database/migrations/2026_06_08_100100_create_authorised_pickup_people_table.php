<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authorised_pickup_people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_profile_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('full_name');
            $table->string('phone', 32);
            $table->string('relationship_note')->nullable();
            $table->string('id_number', 64)->nullable();
            $table->timestamps();

            $table->unique('customer_profile_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authorised_pickup_people');
    }
};
