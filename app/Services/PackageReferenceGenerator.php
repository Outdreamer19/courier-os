<?php

namespace App\Services;

use App\Models\Package;
use Illuminate\Support\Facades\DB;

/**
 * Generates the next unique package reference (e.g. PKG-000001).
 */
class PackageReferenceGenerator
{
    public function next(): string
    {
        return DB::transaction(function (): string {
            $prefix = (string) config('shipdjm.package_reference.prefix', 'PKG');
            $padding = (int) config('shipdjm.package_reference.padding', 6);

            $latest = Package::query()
                ->where('package_reference', 'like', $prefix.'-%')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $nextNumber = 1;

            if ($latest) {
                $suffix = substr($latest->package_reference, strlen($prefix) + 1);

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
