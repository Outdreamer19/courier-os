<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('authorised_pickup_people', function (Blueprint $table) {
            $table->index('customer_profile_id', 'authorised_pickup_people_customer_profile_id_index');
        });

        Schema::table('authorised_pickup_people', function (Blueprint $table) {
            $table->dropUnique(['customer_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::table('authorised_pickup_people', function (Blueprint $table) {
            $table->unique('customer_profile_id');
        });

        Schema::table('authorised_pickup_people', function (Blueprint $table) {
            $table->dropIndex('authorised_pickup_people_customer_profile_id_index');
        });
    }
};
