<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue';
import type { TourStop } from '@/types/marketing';

const props = withDefaults(
    defineProps<{
        stops: TourStop[];
        /** Milliseconds each stop is shown before advancing. 0 disables. */
        interval?: number;
    }>(),
    { interval: 7000 },
);

const active = ref(0);
/**
 * Autoplay is a convenience, not the point of the section — the first
 * deliberate interaction (or a hover, or a hidden tab) stops it for good so
 * the panel never moves out from under someone who is reading it.
 */
const autoplay = ref(true);
const progressKey = ref(0);

let timer: ReturnType<typeof setInterval> | undefined;

const prefersReducedMotion = () =>
    typeof window !== 'undefined' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const stop = () => {
    autoplay.value = false;
    clearInterval(timer);
    timer = undefined;
};

const start = () => {
    if (timer || !props.interval || prefersReducedMotion()) {
        return;
    }

    timer = setInterval(() => {
        active.value = (active.value + 1) % props.stops.length;
        progressKey.value += 1;
    }, props.interval);
};

const select = (index: number) => {
    stop();
    active.value = index;
};

/** Left/right arrows move between tabs, per the ARIA tabs pattern. */
const onKeydown = (event: KeyboardEvent) => {
    const last = props.stops.length - 1;

    if (event.key === 'ArrowRight') {
        select(active.value === last ? 0 : active.value + 1);
    } else if (event.key === 'ArrowLeft') {
        select(active.value === 0 ? last : active.value - 1);
    } else {
        return;
    }

    event.preventDefault();
};

watch(autoplay, (on) => (on ? start() : undefined), { immediate: true });

onBeforeUnmount(() => clearInterval(timer));
</script>

<template>
    <div
        class="mt-12"
        @mouseenter="stop"
        @focusin="stop"
        @touchstart.passive="stop"
    >
        <!-- Tabs -->
        <div
            role="tablist"
            aria-label="Product tour"
            class="-mx-4 flex snap-x snap-mandatory gap-2 overflow-x-auto px-4 pb-2 sm:mx-0 sm:flex-wrap sm:justify-center sm:overflow-visible sm:px-0"
            @keydown="onKeydown"
        >
            <button
                v-for="(item, index) in stops"
                :id="`tour-tab-${item.id}`"
                :key="item.id"
                role="tab"
                type="button"
                :aria-selected="active === index"
                :aria-controls="`tour-panel-${item.id}`"
                :tabindex="active === index ? 0 : -1"
                class="relative shrink-0 snap-start overflow-hidden rounded-full border px-4 py-2 text-sm font-medium whitespace-nowrap transition duration-300 focus-visible:ring-2 focus-visible:ring-marketing-orange focus-visible:ring-offset-2 focus-visible:outline-none"
                :class="
                    active === index
                        ? 'border-marketing-ink bg-marketing-ink text-marketing-cream'
                        : 'border-marketing-border bg-white text-marketing-ink-muted hover:border-marketing-ink/25 hover:text-marketing-ink'
                "
                @click="select(index)"
            >
                {{ item.label }}
                <!-- Autoplay countdown, drawn only on the live tab. -->
                <span
                    v-if="active === index && autoplay"
                    :key="progressKey"
                    class="tour-progress absolute inset-x-0 bottom-0 h-0.5 bg-marketing-amber"
                    :style="{ animationDuration: `${interval}ms` }"
                    aria-hidden="true"
                />
            </button>
        </div>

        <!-- Panels -->
        <div
            v-for="(item, index) in stops"
            v-show="active === index"
            :id="`tour-panel-${item.id}`"
            :key="item.id"
            role="tabpanel"
            :aria-labelledby="`tour-tab-${item.id}`"
            tabindex="0"
            class="mt-8 grid items-center gap-8 focus-visible:outline-none lg:grid-cols-[minmax(0,1fr)_minmax(0,1.35fr)] lg:gap-12"
        >
            <Transition name="tour-copy" appear>
                <div :key="item.id">
                    <h3
                        class="text-2xl font-semibold tracking-tight text-balance sm:text-3xl"
                    >
                        {{ item.title }}
                    </h3>
                    <p
                        class="mt-4 text-base leading-relaxed text-pretty text-marketing-ink-muted"
                    >
                        {{ item.body }}
                    </p>
                    <ul class="mt-6 space-y-3">
                        <li
                            v-for="(bullet, bulletIndex) in item.bullets"
                            :key="bullet"
                            class="tour-bullet flex items-start gap-3 text-sm"
                            :style="{
                                animationDelay: `${120 + bulletIndex * 90}ms`,
                            }"
                        >
                            <span
                                class="mt-1.5 size-1.5 shrink-0 rounded-full bg-marketing-orange"
                                aria-hidden="true"
                            />
                            <span>{{ bullet }}</span>
                        </li>
                    </ul>
                </div>
            </Transition>

            <Transition name="tour-shot" appear>
                <div
                    :key="item.id"
                    class="overflow-hidden rounded-2xl border border-marketing-border bg-white shadow-[0_30px_80px_-35px_rgba(33,13,2,0.5)]"
                >
                    <div
                        class="flex items-center gap-1.5 border-b border-marketing-border bg-marketing-eggshell/60 px-4 py-2.5"
                    >
                        <span class="size-2.5 rounded-full bg-marketing-orange/50" />
                        <span class="size-2.5 rounded-full bg-marketing-amber/60" />
                        <span class="size-2.5 rounded-full bg-marketing-olive/40" />
                    </div>
                    <picture>
                        <source
                            v-if="item.webp"
                            :srcset="item.webp"
                            type="image/webp"
                        />
                        <img
                            :src="item.image"
                            :alt="item.alt"
                            width="1440"
                            height="900"
                            loading="lazy"
                            decoding="async"
                            class="block w-full"
                        />
                    </picture>
                </div>
            </Transition>
        </div>
    </div>
</template>

<style scoped>
@keyframes tour-progress {
    from {
        transform: scaleX(0);
    }
    to {
        transform: scaleX(1);
    }
}

.tour-progress {
    transform-origin: left;
    animation-name: tour-progress;
    animation-timing-function: linear;
    animation-fill-mode: forwards;
}

@keyframes tour-bullet-in {
    from {
        opacity: 0;
        transform: translateX(-8px);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

.tour-bullet {
    opacity: 0;
    animation: tour-bullet-in 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.tour-copy-enter-active,
.tour-shot-enter-active {
    transition:
        opacity 420ms cubic-bezier(0.22, 1, 0.36, 1),
        transform 420ms cubic-bezier(0.22, 1, 0.36, 1);
}

.tour-copy-enter-from {
    opacity: 0;
    transform: translateY(10px);
}

.tour-shot-enter-from {
    opacity: 0;
    transform: translateY(16px) scale(0.985);
}

@media (prefers-reduced-motion: reduce) {
    .tour-progress,
    .tour-bullet {
        animation: none;
        opacity: 1;
    }

    .tour-copy-enter-active,
    .tour-shot-enter-active {
        transition: none;
    }
}
</style>
