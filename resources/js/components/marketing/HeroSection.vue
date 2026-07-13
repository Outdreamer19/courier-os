<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import ProductScreenshotFrame from './ProductScreenshotFrame.vue';

const tabs = [
    {
        key: 'admin',
        label: 'Admin Dashboard',
        screenshotLabel: 'Admin dashboard screenshot',
    },
    {
        key: 'portal',
        label: 'Customer Portal',
        screenshotLabel: 'Customer portal screenshot',
    },
];

const active = ref(0);
let timer: ReturnType<typeof setInterval> | undefined;

onMounted(() => {
    timer = setInterval(() => {
        active.value = (active.value + 1) % tabs.length;
    }, 4500);
});

onUnmounted(() => clearInterval(timer));
</script>

<template>
    <section
        id="top"
        class="fade-in-section relative overflow-hidden bg-marketing-cream"
    >
        <div
            class="mx-auto max-w-5xl px-4 pt-20 pb-16 text-center sm:px-6 lg:px-8"
        >
            <span
                class="inline-flex items-center gap-2 rounded-full border border-marketing-border bg-white px-4 py-1.5 text-xs font-medium text-marketing-ink shadow-sm"
            >
                <span
                    class="rounded-full bg-marketing-orange px-2 py-0.5 text-[10px] font-bold tracking-wide text-white uppercase"
                >
                    New
                </span>
                Now onboarding Caribbean courier businesses
            </span>

            <h1
                class="mx-auto mt-6 max-w-3xl text-5xl leading-[1.05] font-semibold tracking-tight text-balance text-marketing-ink sm:text-6xl"
            >
                Run Your Courier Business Like a Platform
            </h1>

            <p class="mx-auto mt-5 max-w-xl text-lg text-marketing-ink-muted">
                Pre-alerts, package tracking, billing, and customer updates — on
                your own branded site. Launch in minutes, not months.
            </p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a
                    href="#product"
                    class="rounded-lg border border-marketing-border bg-white px-6 py-3 text-sm font-semibold text-marketing-ink transition hover:bg-marketing-eggshell"
                >
                    See How It Works
                </a>
                <a
                    href="/signup"
                    class="inline-flex items-center gap-2 rounded-lg bg-marketing-amber px-6 py-3 text-sm font-semibold text-marketing-ink transition hover:brightness-95"
                >
                    Start Free Trial →
                </a>
            </div>
        </div>

        <div class="mx-auto max-w-5xl px-4 pb-20 sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-center gap-2">
                <button
                    v-for="(tab, index) in tabs"
                    :key="tab.key"
                    type="button"
                    class="rounded-full px-4 py-2 text-sm font-medium transition"
                    :class="
                        active === index
                            ? 'bg-marketing-periwinkle text-marketing-ink'
                            : 'text-marketing-ink-muted hover:bg-marketing-eggshell'
                    "
                    @click="active = index"
                >
                    {{ tab.label }}
                </button>
            </div>
            <ProductScreenshotFrame
                :alt="tabs[active].label"
                :label="tabs[active].screenshotLabel"
                aspect="aspect-[16/9]"
            />
        </div>
    </section>
</template>
