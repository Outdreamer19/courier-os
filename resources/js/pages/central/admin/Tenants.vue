<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PlatformLayout from '@/layouts/PlatformLayout.vue';

type TenantRow = {
    id: number;
    name: string;
    subdomain: string;
    custom_domain: string | null;
    url: string | null;
    status: string;
    currency: string;
    subscription_status: string;
    customers: number;
    packages: number;
    packages_30d: number;
    last_activity_human: string | null;
    health: string;
    created_at: string;
};

type Filter = 'all' | 'active' | 'pending' | 'suspended';

const props = defineProps<{ tenants: TenantRow[] }>();

const search = ref('');
const filter = ref<Filter>('all');

const filtered = computed(() =>
    props.tenants.filter((tenant) => {
        const matchesFilter =
            filter.value === 'all' || tenant.status === filter.value;

        const term = search.value.trim().toLowerCase();
        const matchesSearch =
            term === '' ||
            tenant.name.toLowerCase().includes(term) ||
            tenant.subdomain.toLowerCase().includes(term) ||
            (tenant.custom_domain ?? '').toLowerCase().includes(term);

        return matchesFilter && matchesSearch;
    }),
);

const counts = computed(() => ({
    all: props.tenants.length,
    active: props.tenants.filter((t) => t.status === 'active').length,
    pending: props.tenants.filter((t) => t.status === 'pending').length,
    suspended: props.tenants.filter((t) => t.status === 'suspended').length,
}));

const statusColor = (status: string) =>
    ({
        active: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        pending: 'bg-amber-50 text-amber-700 ring-amber-600/20',
        suspended: 'bg-rose-50 text-rose-700 ring-rose-600/20',
        cancelled: 'bg-zinc-50 text-zinc-600 ring-zinc-500/20',
    })[status] ?? 'bg-zinc-50 text-zinc-600 ring-zinc-500/20';

const healthDot = (health: string) =>
    ({
        healthy: 'bg-emerald-500',
        quiet: 'bg-amber-500',
        at_risk: 'bg-rose-500',
        onboarding: 'bg-sky-500',
        offline: 'bg-zinc-400',
    })[health] ?? 'bg-zinc-400';

const billingLabel = (status: string) =>
    status === 'none' ? 'Not billed' : status.replace('_', ' ');

const toggle = (id: number, status: string) => {
    const action = status === 'suspended' ? 'activate' : 'suspend';

    if (
        action === 'suspend' &&
        !window.confirm(
            'Suspending locks every staff member and customer of this tenant out of the app. Continue?',
        )
    ) {
        return;
    }

    router.patch(
        `/platform/tenants/${id}`,
        { action },
        { preserveScroll: true },
    );
};

const tabs: { key: Filter; label: string }[] = [
    { key: 'all', label: 'All' },
    { key: 'active', label: 'Live' },
    { key: 'pending', label: 'Onboarding' },
    { key: 'suspended', label: 'Suspended' },
];
</script>

<template>
    <Head title="Tenants" />

    <PlatformLayout current="tenants">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl/8 font-semibold text-zinc-950 sm:text-xl/8">
                    Tenants
                </h1>
                <p class="mt-1 text-sm/6 text-zinc-500">
                    {{ tenants.length }}
                    {{ tenants.length === 1 ? 'business' : 'businesses' }} on the
                    platform.
                </p>
            </div>

            <input
                v-model="search"
                type="search"
                placeholder="Search name or domain…"
                class="w-full max-w-xs rounded-lg border border-zinc-950/10 bg-white px-3 py-2 text-sm text-zinc-950 shadow-xs placeholder:text-zinc-400 focus:border-zinc-950/20 focus:outline-none focus:ring-2 focus:ring-zinc-950/10"
            />
        </div>

        <div class="mt-6 flex flex-wrap gap-1">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                type="button"
                class="rounded-lg px-3 py-1.5 text-sm font-medium transition"
                :class="
                    filter === tab.key
                        ? 'bg-zinc-950 text-white'
                        : 'text-zinc-500 hover:bg-zinc-950/5 hover:text-zinc-950'
                "
                @click="filter = tab.key"
            >
                {{ tab.label }}
                <span
                    class="ml-1 text-xs"
                    :class="
                        filter === tab.key ? 'text-white/60' : 'text-zinc-400'
                    "
                    >{{ counts[tab.key] }}</span
                >
            </button>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-left text-sm/6 text-zinc-950">
                <thead class="border-b border-zinc-950/10 text-zinc-500">
                    <tr>
                        <th class="py-3 pr-4 pl-0 font-medium">Business</th>
                        <th class="px-4 py-3 font-medium">Address</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Billing</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Customers
                        </th>
                        <th class="px-4 py-3 text-right font-medium">
                            Packages (30d)
                        </th>
                        <th class="px-4 py-3 font-medium">Last activity</th>
                        <th class="px-4 py-3 font-medium">Joined</th>
                        <th class="py-3 pr-0 pl-4 text-right font-medium">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="tenant in filtered"
                        :key="tenant.id"
                        class="border-b border-zinc-950/5 last:border-0"
                    >
                        <td class="py-4 pr-4 pl-0">
                            <span class="flex items-center gap-2">
                                <span
                                    class="size-1.5 shrink-0 rounded-full"
                                    :class="healthDot(tenant.health)"
                                    :title="tenant.health"
                                />
                                <span class="font-medium">{{
                                    tenant.name
                                }}</span>
                            </span>
                        </td>
                        <td class="px-4 py-4 text-zinc-600">
                            <a
                                v-if="tenant.url"
                                :href="tenant.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="hover:text-zinc-950 hover:underline"
                            >
                                {{
                                    tenant.custom_domain ?? tenant.subdomain
                                }}
                            </a>
                            <span v-else>{{ tenant.subdomain }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <span
                                class="inline-flex rounded-md px-1.5 py-0.5 text-xs font-medium capitalize ring-1 ring-inset"
                                :class="statusColor(tenant.status)"
                            >
                                {{ tenant.status }}
                            </span>
                        </td>
                        <td class="px-4 py-4 capitalize text-zinc-600">
                            {{ billingLabel(tenant.subscription_status) }}
                        </td>
                        <td class="px-4 py-4 text-right tabular-nums">
                            {{ tenant.customers }}
                        </td>
                        <td class="px-4 py-4 text-right tabular-nums">
                            {{ tenant.packages_30d }}
                            <span class="text-xs text-zinc-400"
                                >/ {{ tenant.packages }}</span
                            >
                        </td>
                        <td class="px-4 py-4 text-zinc-500">
                            {{ tenant.last_activity_human ?? '—' }}
                        </td>
                        <td class="px-4 py-4 text-zinc-500">
                            {{ tenant.created_at }}
                        </td>
                        <td class="py-4 pr-0 pl-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <Link
                                    :href="`/platform/support?tenant_id=${tenant.id}`"
                                    class="rounded-lg border border-zinc-950/10 px-2.5 py-1.5 text-xs font-medium text-zinc-700 transition hover:bg-zinc-950/5"
                                >
                                    Message
                                </Link>
                                <button
                                    v-if="tenant.status !== 'pending'"
                                    type="button"
                                    class="rounded-lg border border-zinc-950/10 px-2.5 py-1.5 text-xs font-medium text-zinc-700 transition hover:bg-zinc-950/5"
                                    @click="toggle(tenant.id, tenant.status)"
                                >
                                    {{
                                        tenant.status === 'suspended'
                                            ? 'Reactivate'
                                            : 'Suspend'
                                    }}
                                </button>
                                <span
                                    v-else
                                    class="text-xs text-zinc-400"
                                    >Awaiting checkout</span
                                >
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filtered.length === 0">
                        <td
                            colspan="9"
                            class="py-12 text-center text-sm text-zinc-500"
                        >
                            {{
                                tenants.length === 0
                                    ? 'No tenants yet. Your first signup will appear here.'
                                    : 'No tenants match that filter.'
                            }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </PlatformLayout>
</template>
