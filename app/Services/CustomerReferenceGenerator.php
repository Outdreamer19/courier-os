<?php

namespace App\Services;

use App\Models\CustomerProfile;
use Illuminate\Support\Facades\DB;

/**
 * Generates the next unique customer reference (e.g. DJM-000001).
 *
 * Uses a row-level lock on the latest customer_profiles row to keep concurrent
 * registrations from producing duplicates. The prefix and padding width come
 * from config/shipdjm.php so the format can be changed without code changes.
 */
class CustomerReferenceGenerator
{
    public function next(): string
    {
        return DB::transaction(function (): string {
            $prefix = (string) config('shipdjm.customer_reference.prefix', 'DJM');
            $padding = (int) config('shipdjm.customer_reference.padding', 6);

            $latest = CustomerProfile::query()
                ->where('customer_reference', 'like', $prefix.'-%')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $nextNumber = 1;

            if ($latest) {
                $suffix = substr($latest->customer_reference, strlen($prefix) + 1);

                if (ctype_digit($suffix)) {
                    $nextNumber = ((int) $suffix) + 1;
                }
            }

            return sprintf(
                '%s-%s',
                $prefix,
                str_pad((string) $nextNumber, $padding, '0', STR_PAD_LEFT),
            );
        });
    }
}
