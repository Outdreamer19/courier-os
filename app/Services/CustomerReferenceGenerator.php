<?php

namespace App\Services;

use App\Models\CustomerProfile;
use Illuminate\Support\Facades\DB;

/**
 * Generates unique customer references (e.g. SJM-483927).
 *
 * New references use a random numeric suffix so customer count is not
 * obvious from the reference alone. Existing sequential references are
 * left unchanged.
 */
class CustomerReferenceGenerator
{
    private const MAX_ATTEMPTS = 50;

    public function next(): string
    {
        return DB::transaction(function (): string {
            $prefix = (string) config('shipdjm.customer_reference.prefix', 'SJM');
            $length = (int) config('shipdjm.customer_reference.random_length', 6);

            CustomerProfile::query()->lockForUpdate()->latest('id')->value('id');

            for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
                $suffix = $this->randomSuffix($length);
                $reference = sprintf('%s-%s', $prefix, $suffix);

                if (! $this->referenceExists($reference)) {
                    return $reference;
                }
            }

            throw new \RuntimeException('Unable to generate a unique customer reference.');
        });
    }

    private function randomSuffix(int $length): string
    {
        $min = (int) str_pad('1', $length, '0');
        $max = (int) str_repeat('9', $length);

        return (string) random_int($min, $max);
    }

    private function referenceExists(string $reference): bool
    {
        return CustomerProfile::query()
            ->where('customer_reference', $reference)
            ->exists();
    }
}
