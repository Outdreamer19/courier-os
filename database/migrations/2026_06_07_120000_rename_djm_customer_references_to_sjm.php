<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('customer_profiles')
            ->where('customer_reference', 'like', 'DJM-%')
            ->update([
                'customer_reference' => DB::raw("REPLACE(customer_reference, 'DJM-', 'SJM-')"),
            ]);
    }

    public function down(): void
    {
        DB::table('customer_profiles')
            ->where('customer_reference', 'like', 'SJM-%')
            ->update([
                'customer_reference' => DB::raw("REPLACE(customer_reference, 'SJM-', 'DJM-')"),
            ]);
    }
};
