<script setup lang="ts">
import {
    Bell,
    Box,
    ClipboardList,
    LayoutDashboard,
    PieChart,
    Settings,
    Users,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useInViewLoop } from '@/composables/useInViewLoop';
import ScaleToFit from './ScaleToFit.vue';

/**
 * The operator's side: a crisp, on-brand stand-in for the admin dashboard,
 * laid out at desktop size and scaled. New packages land in the intake list
 * on a loop so the screen reads as a business that is running, not a static
 * screenshot.
 */
withDefaults(defineProps<{ bare?: boolean }>(), { bare: false });

const root = ref<HTMLElement | null>(null);

const incoming = [
    {
        ref: 'PKG-1051',
        name: 'Kerry-Ann Brown',
        from: 'Amazon',
        lb: '2.4',
        status: 'At Miami',
    },
    {
        ref: 'PKG-1050',
        name: 'Dwayne Clarke',
        from: 'Temu',
        lb: '6.0',
        status: 'In transit',
    },
    {
        ref: 'PKG-1049',
        name: 'Tash Morgan',
        from: 'SHEIN',
        lb: '3.1',
        status: 'Ready',
    },
    {
        ref: 'PKG-1048',
        name: 'Marlon Grant',
        from: 'Walmart',
        lb: '11.5',
        status: 'Customs',
    },
    {
        ref: 'PKG-1047',
        name: 'Shauna Reid',
        from: 'Fashion Nova',
        lb: '1.8',
        status: 'Ready',
    },
    {
        ref: 'PKG-1046',
        name: 'Andre Thomas',
        from: 'eBay',
        lb: '4.2',
        status: 'Picked up',
    },
    {
        ref: 'PKG-1045',
        name: 'Nordia Blake',
        from: 'Amazon',
        lb: '0.9',
        status: 'In transit',
    },
];

const { step, seen } = useInViewLoop(root, {
    steps: 4,
    interval: 2400,
    hold: 2400,
});

const rows = computed(() => {
    const offset = 3 - step.value;

    return incoming.slice(offset, offset + 5);
});

const statusTone: Record<string, string> = {
    'At Miami': 'bg-[#c6dffa]/70 text-[#1e4f80]',
    'In transit': 'bg-marketing-amber/35 text-marketing-olive',
    Customs: 'bg-marketing-lavender/80 text-[#4c3a8a]',
    Ready: 'bg-[#25d366]/15 text-[#128c4a]',
    'Picked up': 'bg-marketing-eggshell text-marketing-ink-muted',
};

const stats = [
    { label: 'Packages this month', value: '412', note: '+38 this week' },
    { label: 'Ready for pickup', value: '63', note: 'Customers notified' },
    { label: 'Pre-alerts to match', value: '9', note: 'Auto matched 128' },
    {
        label: 'Collected this month',
        value: 'JMD 1.28M',
        note: '+22% vs last month',
    },
];

const bars = [42, 55, 48, 63, 58, 74, 69, 81, 77, 92, 86, 100];
const months = ['N', 'D', 'J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O'];

const nav = [
    { icon: LayoutDashboard, label: 'Dashboard', active: true },
    { icon: Box, label: 'Packages' },
    { icon: ClipboardList, label: 'Pre-alerts' },
    { icon: Users, label: 'Customers' },
    { icon: PieChart, label: 'Reports' },
    { icon: Settings, label: 'Settings' },
];
</script>

<template>
    <div ref="root">
        <ScaleToFit :width="1100" :height="690">
            <div
                class="flex h-full w-full overflow-hidden bg-[#fbfaf7] text-marketing-ink"
                :class="
                    bare
                        ? ''
                        : 'rounded-[22px] border border-marketing-ink/10 shadow-[0_50px_100px_-40px_rgba(33,13,2,0.45)]'
                "
                role="img"
                aria-label="The CourierOS admin dashboard: packages this month, ready for pickup, pre-alerts to match, money collected, a monthly volume chart and new packages arriving"
            >
                <!-- Sidebar -->
                <aside
                    class="flex w-[210px] shrink-0 flex-col bg-marketing-ink px-4 py-5 text-white"
                >
                    <div class="flex items-center gap-2.5 px-2">
                        <span
                            class="flex size-9 items-center justify-center rounded-[10px] bg-marketing-amber text-sm font-bold text-marketing-ink"
                            >YC</span
                        >
                        <div>
                            <p class="text-[14px] leading-tight font-semibold">
                                Your Courier
                            </p>
                            <p class="text-[11px] text-white/50">Admin</p>
                        </div>
                    </div>
                    <nav class="mt-7 space-y-1">
                        <div
                            v-for="item in nav"
                            :key="item.label"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px]"
                            :class="
                                item.active
                                    ? 'bg-white/12 font-semibold text-white'
                                    : 'text-white/55'
                            "
                        >
                            <component :is="item.icon" class="size-4" />
                            {{ item.label }}
                        </div>
                    </nav>
                    <div class="mt-auto rounded-xl bg-white/[0.07] p-3">
                        <p class="text-[11px] text-white/50">Your site</p>
                        <p class="text-[12px] font-medium">
                            yourcourier.courieros.co
                        </p>
                    </div>
                </aside>

                <!-- Main -->
                <main class="flex min-w-0 flex-1 flex-col gap-4 p-6">
                    <header class="flex items-center justify-between">
                        <div>
                            <p class="text-[20px] font-semibold tracking-tight">
                                Good morning, Andre
                            </p>
                            <p class="text-[13px] text-marketing-ink-muted">
                                Here is the business today.
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span
                                class="relative flex size-9 items-center justify-center rounded-full border border-marketing-ink/10 bg-white"
                            >
                                <Bell class="size-4" />
                                <span
                                    class="absolute top-1.5 right-2 size-2 rounded-full bg-marketing-orange"
                                />
                            </span>
                            <span
                                class="rounded-lg bg-marketing-ink px-4 py-2.5 text-[13px] font-semibold text-white"
                                >Receive package</span
                            >
                        </div>
                    </header>

                    <div class="grid grid-cols-4 gap-3">
                        <div
                            v-for="(stat, i) in stats"
                            :key="stat.label"
                            class="stat rounded-2xl border border-marketing-ink/8 bg-white p-4"
                            :class="{ 'is-in': seen }"
                            :style="{ transitionDelay: `${i * 90}ms` }"
                        >
                            <p class="text-[12px] text-marketing-ink-muted">
                                {{ stat.label }}
                            </p>
                            <p
                                class="mt-1.5 text-[26px] leading-none font-semibold tracking-tight tabular-nums"
                            >
                                {{ stat.value }}
                            </p>
                            <p
                                class="mt-2 text-[11px] font-medium text-[#128c4a]"
                            >
                                {{ stat.note }}
                            </p>
                        </div>
                    </div>

                    <div class="grid min-h-0 flex-1 grid-cols-5 gap-3">
                        <section
                            class="col-span-2 flex flex-col rounded-2xl border border-marketing-ink/8 bg-white p-4"
                        >
                            <div class="flex items-baseline justify-between">
                                <p class="text-[14px] font-semibold">
                                    Packages per month
                                </p>
                                <p class="text-[11px] text-marketing-ink-muted">
                                    Last 12 months
                                </p>
                            </div>
                            <div class="mt-4 flex flex-1 items-end gap-2">
                                <div
                                    v-for="(bar, i) in bars"
                                    :key="i"
                                    class="flex h-full flex-1 flex-col items-center justify-end gap-1.5"
                                >
                                    <div
                                        class="bar w-full rounded-t-md"
                                        :class="[
                                            i === bars.length - 1
                                                ? 'bg-marketing-orange'
                                                : 'bg-marketing-amber',
                                            { 'is-in': seen },
                                        ]"
                                        :style="{
                                            height: `${bar}%`,
                                            transitionDelay: `${200 + i * 45}ms`,
                                        }"
                                    />
                                    <span
                                        class="text-[10px] text-marketing-ink-muted"
                                        >{{ months[i] }}</span
                                    >
                                </div>
                            </div>
                        </section>

                        <section
                            class="col-span-3 flex flex-col overflow-hidden rounded-2xl border border-marketing-ink/8 bg-white p-4"
                        >
                            <div class="flex items-baseline justify-between">
                                <p class="text-[14px] font-semibold">
                                    Just received
                                </p>
                                <p
                                    class="flex items-center gap-1.5 text-[11px] text-marketing-ink-muted"
                                >
                                    <span
                                        class="size-1.5 animate-pulse rounded-full bg-[#25d366]"
                                    />
                                    Live
                                </p>
                            </div>
                            <TransitionGroup
                                tag="div"
                                name="row"
                                class="relative mt-2"
                            >
                                <div
                                    v-for="row in rows"
                                    :key="row.ref"
                                    class="flex items-center gap-3 border-b border-marketing-ink/6 py-2.5 last:border-0"
                                >
                                    <span
                                        class="flex size-8 shrink-0 items-center justify-center rounded-full bg-marketing-eggshell text-[11px] font-semibold"
                                    >
                                        {{
                                            row.name
                                                .split(' ')
                                                .map((p) => p[0])
                                                .join('')
                                        }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[13px] font-medium">
                                            {{ row.name }}
                                        </p>
                                        <p
                                            class="text-[11px] text-marketing-ink-muted tabular-nums"
                                        >
                                            {{ row.ref }} · {{ row.from }} ·
                                            {{ row.lb }} lb
                                        </p>
                                    </div>
                                    <span
                                        class="rounded-full px-2.5 py-1 text-[11px] font-semibold"
                                        :class="statusTone[row.status]"
                                        >{{ row.status }}</span
                                    >
                                </div>
                            </TransitionGroup>
                        </section>
                    </div>
                </main>
            </div>
        </ScaleToFit>
    </div>
</template>

<style scoped>
.stat {
    opacity: 0;
    transform: translateY(10px);
    transition:
        opacity 0.5s ease-out,
        transform 0.5s cubic-bezier(0.22, 1, 0.36, 1);
}

.stat.is-in {
    opacity: 1;
    transform: none;
}

.bar {
    transform: scaleY(0);
    transform-origin: bottom;
    transition: transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
}

.bar.is-in {
    transform: scaleY(1);
}

.row-enter-active {
    transition:
        opacity 0.45s ease-out,
        transform 0.45s cubic-bezier(0.22, 1, 0.36, 1),
        background-color 1.6s ease-out;
}

.row-enter-from {
    opacity: 0;
    transform: translateY(-12px);
    background-color: rgba(252, 213, 25, 0.35);
}

.row-leave-active {
    position: absolute;
    left: 0;
    right: 0;
    transition: opacity 0.3s ease-out;
}

.row-leave-to {
    opacity: 0;
}

.row-move {
    transition: transform 0.45s cubic-bezier(0.22, 1, 0.36, 1);
}

@media (prefers-reduced-motion: reduce) {
    .stat,
    .bar {
        opacity: 1;
        transform: none;
        transition: none;
    }
}
</style>
