<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    stats: {
        total_tenants: number;
        active_tenants: number;
        suspended_tenants: number;
        pending_tenants: number;
        active_subscriptions: number;
        mrr: number;
    };
    signups: { month: string; count: number }[];
}>();

const formatMonth = (m: string) => {
    const [y, mo] = m.split('-');
    return new Date(Number(y), Number(mo) - 1).toLocaleString('en', {
        month: 'short',
    });
};
</script>

<template>
    <Head title="Platform overview — CourierOS" />

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
                <span class="text-white/90">Overview</span>
                <Link href="/platform/tenants" class="text-white/50 hover:text-white"
                    >Tenants</Link
                >
            </nav>
        </header>

        <main class="mx-auto max-w-6xl px-8 py-10">
            <h1 class="text-2xl font-semibold tracking-tight">
                Platform overview
            </h1>
            <p class="mt-1 text-sm text-white/50">
                Every courier business running on CourierOS.
            </p>

            <!-- MRR hero -->
            <div
                class="mt-8 overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-br from-[hsl(168_76%_20%)] to-[hsl(222_47%_14%)] p-8"
            >
                <p
                    class="text-xs font-medium uppercase tracking-[0.2em] text-[hsl(168_76%_60%)]"
                >
                    Monthly recurring revenue
                </p>
                <p class="mt-2 text-5xl font-semibold tracking-tight">
                    ${{ stats.mrr.toLocaleString() }}
                </p>
                <p class="mt-2 text-sm text-white/60">
                    from {{ stats.active_subscriptions }} active
                    {{
                        stats.active_subscriptions === 1
                            ? 'subscription'
                            : 'subscriptions'
                    }}
                </p>
            </div>

            <!-- Stat cards -->
            <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-4">
                <div class="rounded-xl border border-white/10 bg-white/5 p-5">
                    <p class="text-sm text-white/50">Total tenants</p>
                    <p class="mt-1 text-3xl font-semibold">
                        {{ stats.total_tenants }}
                    </p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-5">
                    <p class="text-sm text-white/50">Active</p>
                    <p class="mt-1 text-3xl font-semibold text-[hsl(168_76%_60%)]">
                        {{ stats.active_tenants }}
                    </p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-5">
                    <p class="text-sm text-white/50">Pending</p>
                    <p class="mt-1 text-3xl font-semibold text-amber-300">
                        {{ stats.pending_tenants }}
                    </p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-5">
                    <p class="text-sm text-white/50">Suspended</p>
                    <p class="mt-1 text-3xl font-semibold text-rose-300">
                        {{ stats.suspended_tenants }}
                    </p>
                </div>
            </div>

            <!-- Signups chart -->
            <div class="mt-6 rounded-xl border border-white/10 bg-white/5 p-6">
                <p class="text-sm font-medium text-white/70">
                    Signups, last 6 months
                </p>
                <div class="mt-6 flex items-end gap-4" style="height: 160px">
                    <div
                        v-for="point in signups"
                        :key="point.month"
                        class="flex flex-1 flex-col items-center gap-2"
                    >
                        <div
                            class="w-full rounded-t bg-[hsl(168_76%_42%)] transition-all"
                            :style="{
                                height:
                                    Math.max(
                                        point.count *
                                            (120 /
                                                Math.max(
                                                    ...signups.map(
                                                        (s) => s.count,
                                                    ),
                                                    1,
                                                )),
                                        point.count > 0 ? 8 : 2,
                                    ) + 'px',
                                opacity: point.count > 0 ? 1 : 0.25,
                            }"
                        />
                        <span class="text-xs text-white/40">{{
                            formatMonth(point.month)
                        }}</span>
                        <span class="text-xs font-medium text-white/70">{{
                            point.count
                        }}</span>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
