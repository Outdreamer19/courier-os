<script setup lang="ts">
import type { UrlMethodPair } from '@inertiajs/core';
import { Link } from '@inertiajs/vue3';
import { ArrowDownRight, ArrowUpRight } from 'lucide-vue-next';
import type { Component } from 'vue';
import { computed, onMounted, ref } from 'vue';
import Sparkbars from '@/components/charts/Sparkbars.vue';
import { useAnimatedNumber } from '@/composables/useAnimatedNumber';

type Tone = 'brand' | 'indigo' | 'amber' | 'rose' | 'emerald';

const props = defineProps<{
    title: string;
    value: number;
    helper: string;
    icon: Component;
    tone: Tone;
    href: string | UrlMethodPair;
    /** Monthly series for the mini bar strip. */
    spark?: number[];
    /** 0 to 1, drawn as a slim progress bar when there is no series. */
    progress?: number;
    progressLabel?: string;
    formatter?: (value: number) => string;
    delay?: number;
}>();

const tones: Record<
    Tone,
    { chip: string; text: string; bar: string; well: string }
> = {
    brand: {
        chip: 'bg-primary/10 text-primary',
        text: 'text-primary',
        bar: 'bg-primary',
        well: 'bg-primary/[0.04]',
    },
    indigo: {
        chip: 'bg-indigo-500/10 text-indigo-600',
        text: 'text-indigo-500',
        bar: 'bg-indigo-500',
        well: 'bg-indigo-500/[0.04]',
    },
    amber: {
        chip: 'bg-amber-500/12 text-amber-600',
        text: 'text-amber-500',
        bar: 'bg-amber-500',
        well: 'bg-amber-500/[0.05]',
    },
    rose: {
        chip: 'bg-rose-500/10 text-rose-600',
        text: 'text-rose-500',
        bar: 'bg-rose-500',
        well: 'bg-rose-500/[0.04]',
    },
    emerald: {
        chip: 'bg-emerald-500/10 text-emerald-600',
        text: 'text-emerald-500',
        bar: 'bg-emerald-500',
        well: 'bg-emerald-500/[0.04]',
    },
};

const tone = computed(() => tones[props.tone]);

// Let the progress bar fill from empty once the card is on screen.
const ready = ref(false);
onMounted(() => requestAnimationFrame(() => (ready.value = true)));

const animated = useAnimatedNumber(() => props.value);
const shown = computed(() =>
    props.formatter
        ? props.formatter(animated.value)
        : Math.round(animated.value).toLocaleString(),
);

// Month on month change from the last two points of the series.
const trend = computed(() => {
    const series = props.spark ?? [];

    if (series.length < 2) {
        return null;
    }

    const previous = series[series.length - 2];
    const current = series[series.length - 1];

    if (previous === 0) {
        return current > 0 ? { up: true, label: 'New' } : null;
    }

    const change = ((current - previous) / previous) * 100;

    return { up: change >= 0, label: `${Math.abs(change).toFixed(0)}%` };
});
</script>

<template>
    <Link
        :href="href"
        class="group dash-rise block rounded-2xl border border-border bg-card p-5 transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_18px_40px_-24px_rgba(15,23,42,0.35)] focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
        :style="{ '--rise-delay': `${delay ?? 0}ms` }"
    >
        <div class="flex items-center gap-3">
            <span
                :class="[
                    'flex size-10 shrink-0 items-center justify-center rounded-xl',
                    tone.chip,
                ]"
            >
                <component :is="icon" class="icon-hop size-5" />
            </span>
            <p class="text-sm font-semibold text-foreground">{{ title }}</p>
        </div>

        <div :class="['mt-4 rounded-xl px-4 pt-3.5 pb-3', tone.well]">
            <div class="flex items-baseline justify-between gap-2">
                <p
                    class="text-[26px] leading-none font-bold tracking-tight text-foreground tabular-nums"
                >
                    {{ shown }}
                </p>
                <span
                    v-if="trend"
                    :class="[
                        'inline-flex shrink-0 items-center gap-0.5 rounded-full px-1.5 py-0.5 text-xs font-semibold',
                        trend.up
                            ? 'bg-emerald-500/10 text-emerald-700'
                            : 'bg-rose-500/10 text-rose-700',
                    ]"
                >
                    <ArrowUpRight v-if="trend.up" class="size-3.5" />
                    <ArrowDownRight v-else class="size-3.5" />
                    {{ trend.label }}
                </span>
            </div>
            <Sparkbars
                v-if="spark?.length"
                :data="spark"
                :color-class="tone.text"
                class="mt-3 h-9 w-full"
            />
        </div>

        <p class="mt-3 text-xs text-muted-foreground">{{ helper }}</p>

        <div v-if="progress !== undefined" class="mt-2.5">
            <div class="h-1.5 overflow-hidden rounded-full bg-muted">
                <div
                    :class="[
                        'h-full rounded-full transition-[width] duration-1000 ease-out',
                        tone.bar,
                    ]"
                    :style="{
                        width: ready
                            ? `${Math.round(Math.min(1, Math.max(0, progress)) * 100)}%`
                            : '0%',
                    }"
                />
            </div>
            <p
                v-if="progressLabel"
                class="mt-1.5 text-xs text-muted-foreground"
            >
                {{ progressLabel }}
            </p>
        </div>
    </Link>
</template>
