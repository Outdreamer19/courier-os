<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

defineProps<{
    tenants: {
        id: number;
        name: string;
        subdomain: string;
        custom_domain: string | null;
        status: string;
        currency: string;
        created_at: string;
    }[];
}>();

const statusColor = (status: string) => {
    return (
        {
            active: 'bg-[hsl(168_76%_42%)]/15 text-[hsl(168_76%_70%)]',
            pending: 'bg-amber-400/15 text-amber-300',
            suspended: 'bg-rose-400/15 text-rose-300',
            cancelled: 'bg-white/10 text-white/50',
        }[status] ?? 'bg-white/10 text-white/50'
    );
};

const toggle = (id: number, status: string) => {
    const action = status === 'suspended' ? 'activate' : 'suspend';
    router.patch(`/platform/tenants/${id}`, { action }, { preserveScroll: true });
};
</script>

<template>
    <Head title="Tenants — CourierOS Platform" />

    <div class="min-h-screen bg-[hsl(222_47%_11%)] text-white">
        <header
            class="flex items-center justify-between border-b border-white/10 px-8 py-5"
        >
            <div class="flex items-center gap-2.5">
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-[hsl(168_76%_42%)] font-bold text-[hsl(222_47%_11%)]"
                >
                    C
                </div>
                <span class="font-semibold tracking-tight">CourierOS</span>
                <span class="ml-1 text-xs text-white/40">Platform</span>
            </div>
            <nav class="flex items-center gap-6 text-sm">
                <Link href="/platform" class="text-white/50 hover:text-white"
                    >Overview</Link
                >
                <span class="text-white/90">Tenants</span>
            </nav>
        </header>

        <main class="mx-auto max-w-6xl px-8 py-10">
            <h1 class="text-2xl font-semibold tracking-tight">Tenants</h1>
            <p class="mt-1 text-sm text-white/50">
                {{ tenants.length }}
                {{ tenants.length === 1 ? 'business' : 'businesses' }} on the
                platform.
            </p>

            <div
                class="mt-8 overflow-hidden rounded-xl border border-white/10 bg-white/5"
            >
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/10 text-left text-white/50">
                            <th class="px-5 py-3 font-medium">Business</th>
                            <th class="px-5 py-3 font-medium">Address</th>
                            <th class="px-5 py-3 font-medium">Currency</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Joined</th>
                            <th class="px-5 py-3 font-medium text-right">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="tenant in tenants"
                            :key="tenant.id"
                            class="border-b border-white/5 last:border-0"
                        >
                            <td class="px-5 py-4 font-medium">
                                {{ tenant.name }}
                            </td>
                            <td class="px-5 py-4 text-white/60">
                                {{ tenant.custom_domain ?? tenant.subdomain }}
                            </td>
                            <td class="px-5 py-4 text-white/60">
                                {{ tenant.currency }}
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                    :class="statusColor(tenant.status)"
                                >
                                    {{ tenant.status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-white/50">
                                {{ tenant.created_at }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <button
                                    v-if="tenant.status !== 'pending'"
                                    @click="toggle(tenant.id, tenant.status)"
                                    class="rounded-md border border-white/15 px-3 py-1.5 text-xs font-medium text-white/80 hover:bg-white/10"
                                >
                                    {{
                                        tenant.status === 'suspended'
                                            ? 'Reactivate'
                                            : 'Suspend'
                                    }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="tenants.length === 0">
                            <td
                                colspan="6"
                                class="px-5 py-10 text-center text-white/40"
                            >
                                No tenants yet. Your first signup will appear
                                here.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</template>
