<?php

namespace App\Services;

use App\Models\Package;
use App\Support\Tenancy\TenantConfig;
use Illuminate\Support\Facades\DB;

/**
 * Generates the next unique package reference (e.g. PKG-000001).
 * The prefix is resolved from the current tenant.
 */
class PackageReferenceGenerator
{
    public function __construct(private readonly TenantConfig $config) {}

    public function next(): string
    {
        return DB::transaction(function (): string {
            $prefix = $this->config->packageReferencePrefix();
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
