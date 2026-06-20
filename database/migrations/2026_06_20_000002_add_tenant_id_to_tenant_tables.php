<?php

use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that become tenant-scoped.
     *
     * @var list<string>
     */
    private array $tables = [
        'users',
        'customer_profiles',
        'packages',
        'pre_alerts',
        'package_status_histories',
        'shipping_rates',
        'contact_messages',
        'warehouse_addresses',
        'activity_logs',
        'authorised_pickup_people',
    ];

    public function up(): void
    {
        // Ensure a tenant exists to own any pre-existing rows so production
        // data survives the move to multi-tenancy. The canonical Ship'd JM
        // tenant adopts everything that existed before this migration.
        $tenantId = $this->resolveBackfillTenantId();

        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $blueprint->index('tenant_id');
            });

            if ($tenantId !== null) {
                DB::table($table)->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropIndex([$table = $blueprint->getTable().'_tenant_id_index']);
            });

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('tenant_id');
            });
        }
    }

    /**
     * Find or create the tenant that should own pre-existing rows.
     * Only creates one if there is legacy data to adopt.
     */
    private function resolveBackfillTenantId(): ?int
    {
        $hasLegacyData = collect($this->tables)
            ->filter(fn ($table) => Schema::hasTable($table))
            ->contains(fn ($table) => DB::table($table)->exists());

        if (! $hasLegacyData) {
            return null;
        }

        $existing = DB::table('tenants')->where('subdomain', 'shipdjm')->value('id');

        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('tenants')->insertGetId([
            'name' => "Ship'd JM",
            'subdomain' => 'shipdjm',
            'status' => Tenant::STATUS_ACTIVE,
            'currency' => 'JMD',
            'customer_reference_prefix' => 'SJM',
            'package_reference_prefix' => 'PKG',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
