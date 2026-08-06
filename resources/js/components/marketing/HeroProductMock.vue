<script setup lang="ts">
/**
 * A rendered stand-in for the real product, shown inside the hero's browser
 * frame. Built from live markup rather than an image so it stays crisp at any
 * density, needs no asset pipeline, and can animate — an empty "screenshot
 * coming soon" placeholder is the fastest way to make a landing page feel
 * unfinished.
 *
 * Swap this for real screenshots by passing `src` to ProductScreenshotFrame
 * once marketing captures them.
 */
defineProps<{ view: 'admin' | 'portal' }>();

const adminStats = [
    { label: 'Packages in warehouse', value: '184', delta: '+12 today' },
    { label: 'Awaiting pre-alert match', value: '23', delta: '4 overdue' },
    { label: 'Ready for pickup', value: '61', delta: '+8 today' },
    { label: 'Outstanding', value: '$248k', delta: 'JMD' },
];

const adminRows = [
    {
        ref: 'PKG-TSL-1042',
        customer: 'Shauna Reid',
        merchant: 'Amazon',
        status: 'Ready for pickup',
        tone: 'green',
    },
    {
        ref: 'PKG-TSL-1041',
        customer: 'Odane Wilson',
        merchant: 'SHEIN',
        status: 'In transit',
        tone: 'amber',
    },
    {
        ref: 'PKG-TSL-1040',
        customer: 'Kadeen Powell',
        merchant: 'Temu',
        status: 'At warehouse',
        tone: 'blue',
    },
    {
        ref: 'PKG-TSL-1039',
        customer: 'Latoya Grant',
        merchant: 'Walmart',
        status: 'Customs',
        tone: 'grey',
    },
    {
        ref: 'PKG-TSL-1038',
        customer: 'Jermaine Palmer',
        merchant: 'eBay',
        status: 'Ready for pickup',
        tone: 'green',
    },
    {
        ref: 'PKG-TSL-1037',
        customer: 'Alecia Simmonds',
        merchant: 'Fashion Nova',
        status: 'In transit',
        tone: 'amber',
    },
    {
        ref: 'PKG-TSL-1036',
        customer: 'Nordia Blake',
        merchant: 'Amazon',
        status: 'Picked up',
        tone: 'grey',
    },
];

const timeline = [
    { label: 'Pre-alert submitted', done: true },
    { label: 'Received at Miami warehouse', done: true },
    { label: 'In transit to Jamaica', done: true },
    { label: 'Ready for pickup', done: false },
];

const toneClasses: Record<string, string> = {
    green: 'bg-emerald-500/12 text-emerald-700',
    amber: 'bg-marketing-amber/25 text-marketing-olive',
    blue: 'bg-marketing-periwinkle/60 text-sky-800',
    grey: 'bg-marketing-eggshell text-marketing-ink-muted',
};

const bars = [38, 52, 44, 67, 58, 81, 72, 94];
</script>

<template>
    <div class="h-full w-full bg-marketing-cream p-3 text-marketing-ink sm:p-4">
        <!-- ADMIN DASHBOARD -->
        <div v-if="view === 'admin'" class="flex h-full gap-3">
            <!-- Sidebar -->
            <aside
                class="hidden w-36 shrink-0 flex-col rounded-lg bg-marketing-ink/95 p-3 text-white/80 sm:flex"
            >
                <div class="flex items-center gap-1.5">
                    <span
                        class="flex size-4 items-center justify-center rounded bg-marketing-amber text-[8px] font-bold text-marketing-ink"
                        >T</span
                    >
                    <span class="text-[9px] font-semibold">Today Shipping</span>
                </div>
                <div class="mt-3 space-y-1">
                    <div
                        v-for="(item, i) in [
                            'Dashboard',
                            'Packages',
                            'Pre-alerts',
                            'Customers',
                            'Reports',
                            'Settings',
                        ]"
                        :key="item"
                        class="rounded px-1.5 py-1 text-[8px]"
                        :class="
                            i === 1
                                ? 'bg-white/15 text-white'
                                : 'text-white/45'
                        "
                    >
                        {{ item }}
                    </div>
                </div>
            </aside>

            <!-- Main -->
            <div class="flex min-w-0 flex-1 flex-col gap-2.5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-semibold sm:text-xs">
                            Warehouse overview
                        </p>
                        <p class="text-[8px] text-marketing-ink-muted">
                            Monday, 12 August
                        </p>
                    </div>
                    <span
                        class="rounded bg-marketing-amber px-2 py-1 text-[8px] font-semibold"
                        >Intake package</span
                    >
                </div>

                <!-- Stat cards -->
                <div class="grid grid-cols-2 gap-2 lg:grid-cols-4">
                    <div
                        v-for="stat in adminStats"
                        :key="stat.label"
                        class="rounded-lg border border-marketing-border bg-white p-2"
                    >
                        <p
                            class="truncate text-[7px] text-marketing-ink-muted sm:text-[8px]"
                        >
                            {{ stat.label }}
                        </p>
                        <p class="mt-0.5 text-sm font-semibold sm:text-base">
                            {{ stat.value }}
                        </p>
                        <p class="text-[7px] text-emerald-600">
                            {{ stat.delta }}
                        </p>
                    </div>
                </div>

                <div class="grid min-h-0 flex-1 gap-2.5 lg:grid-cols-5">
                    <!-- Package table -->
                    <div
                        class="flex flex-col rounded-lg border border-marketing-border bg-white p-2.5 lg:col-span-3"
                    >
                        <p class="text-[8px] font-semibold sm:text-[9px]">
                            Recent intake
                        </p>
                        <div class="mt-1.5 flex flex-1 flex-col justify-between">
                            <div
                                v-for="row in adminRows"
                                :key="row.ref"
                                class="flex items-center justify-between gap-2 border-b border-marketing-border py-1 last:border-0"
                            >
                                <div class="min-w-0">
                                    <p
                                        class="truncate text-[8px] font-medium tabular-nums"
                                    >
                                        {{ row.ref }}
                                    </p>
                                    <p
                                        class="truncate text-[7px] text-marketing-ink-muted"
                                    >
                                        {{ row.customer }} · {{ row.merchant }}
                                    </p>
                                </div>
                                <span
                                    class="shrink-0 rounded-full px-1.5 py-0.5 text-[7px] font-medium"
                                    :class="toneClasses[row.tone]"
                                >
                                    {{ row.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Volume chart -->
                    <div
                        class="flex flex-col rounded-lg border border-marketing-border bg-white p-2.5 lg:col-span-2"
                    >
                        <p class="text-[8px] font-semibold sm:text-[9px]">
                            Weekly volume
                        </p>
                        <div class="mt-2 flex flex-1 items-end gap-1">
                            <div
                                v-for="(bar, i) in bars"
                                :key="i"
                                class="flex-1 rounded-t"
                                :class="
                                    i === bars.length - 1
                                        ? 'bg-marketing-orange'
                                        : 'bg-marketing-amber/70'
                                "
                                :style="{ height: bar + '%' }"
                            />
                        </div>
                        <p class="mt-1 text-[7px] text-marketing-ink-muted">
                            +18% vs last week
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CUSTOMER PORTAL -->
        <div v-else class="flex h-full flex-col gap-2.5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-semibold sm:text-xs">
                        Welcome back, Shauna
                    </p>
                    <p class="text-[8px] text-marketing-ink-muted">
                        Your reference: TSL-000148
                    </p>
                </div>
                <span
                    class="rounded bg-marketing-amber px-2 py-1 text-[8px] font-semibold"
                    >New pre-alert</span
                >
            </div>

            <div class="grid min-h-0 flex-1 gap-2.5 lg:grid-cols-5">
                <!-- Address card -->
                <div
                    class="rounded-lg border border-marketing-border bg-white p-2.5 lg:col-span-2"
                >
                    <p class="text-[8px] font-semibold">Your Miami address</p>
                    <div
                        class="mt-1.5 space-y-0.5 text-[7.5px] leading-relaxed text-marketing-ink-muted"
                    >
                        <p>Shauna Reid — TSL-000148</p>
                        <p>1234 Logistics Way, Suite TSL</p>
                        <p>Miami, FL 33101</p>
                        <p>+1 (305) 555-0100</p>
                    </div>
                    <div
                        class="mt-2 rounded border border-marketing-border bg-marketing-eggshell/60 px-1.5 py-1 text-[7px] text-marketing-ink-muted"
                    >
                        Copy address
                    </div>

                    <p class="mt-2.5 text-[8px] font-semibold">Amount due</p>
                    <p class="text-sm font-semibold">$4,250 JMD</p>
                    <div
                        class="mt-1 rounded bg-marketing-orange px-1.5 py-1 text-center text-[7px] font-semibold text-white"
                    >
                        Pay invoice
                    </div>
                </div>

                <!-- Tracking timeline -->
                <div
                    class="flex flex-col rounded-lg border border-marketing-border bg-white p-2.5 lg:col-span-3"
                >
                    <div class="flex items-center justify-between">
                        <p class="text-[8px] font-semibold">PKG-TSL-1042</p>
                        <span
                            class="rounded-full bg-emerald-500/12 px-1.5 py-0.5 text-[7px] font-medium text-emerald-700"
                            >On schedule</span
                        >
                    </div>
                    <p class="text-[7px] text-marketing-ink-muted">
                        Amazon · 3.5 lb · arriving Thursday
                    </p>

                    <div class="mt-2.5 space-y-2">
                        <div
                            v-for="(step, i) in timeline"
                            :key="step.label"
                            class="flex items-start gap-1.5"
                        >
                            <div class="flex flex-col items-center">
                                <span
                                    class="size-2 rounded-full"
                                    :class="
                                        step.done
                                            ? 'bg-marketing-orange'
                                            : 'border border-marketing-border bg-white'
                                    "
                                />
                                <span
                                    v-if="i < timeline.length - 1"
                                    class="h-3 w-px"
                                    :class="
                                        step.done
                                            ? 'bg-marketing-orange/40'
                                            : 'bg-marketing-border'
                                    "
                                />
                            </div>
                            <div class="-mt-0.5">
                                <p
                                    class="text-[7.5px] font-medium"
                                    :class="
                                        step.done
                                            ? ''
                                            : 'text-marketing-ink-muted'
                                    "
                                >
                                    {{ step.label }}
                                </p>
                                <p
                                    v-if="step.done"
                                    class="text-[6.5px] text-marketing-ink-muted"
                                >
                                    Updated automatically
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="mt-auto flex items-center gap-1.5 rounded border border-marketing-border bg-marketing-eggshell/50 px-1.5 py-1"
                    >
                        <span class="size-1.5 rounded-full bg-emerald-500" />
                        <p class="text-[7px] text-marketing-ink-muted">
                            WhatsApp update sent to +1 (876) 555-0150
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
