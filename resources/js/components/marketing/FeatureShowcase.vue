<script setup lang="ts">
withDefaults(
    defineProps<{
        eyebrow: string;
        title: string;
        body: string;
        bullets?: string[];
        reverse?: boolean;
    }>(),
    { bullets: () => [], reverse: false },
);
</script>

<template>
    <div
        class="grid items-center gap-12 rounded-3xl border border-marketing-border bg-gradient-to-br from-marketing-lavender/40 via-marketing-orange/10 to-marketing-cream p-8 sm:p-12 lg:grid-cols-2 lg:p-16"
    >
        <div :class="reverse ? 'lg:order-2' : 'lg:order-1'">
            <p
                class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase"
            >
                {{ eyebrow }}
            </p>
            <h3
                class="mt-3 text-3xl leading-tight font-semibold tracking-tight text-marketing-ink sm:text-4xl"
            >
                {{ title }}
            </h3>
            <p
                class="mt-4 max-w-md text-base leading-relaxed text-marketing-ink-muted"
            >
                {{ body }}
            </p>
            <ul v-if="bullets.length" class="mt-6 space-y-3">
                <li
                    v-for="bullet in bullets"
                    :key="bullet"
                    class="flex items-center gap-3 text-sm font-medium text-marketing-ink"
                >
                    <span
                        class="flex size-5 items-center justify-center rounded-full bg-marketing-orange/15 text-marketing-orange"
                    >
                        ✓
                    </span>
                    {{ bullet }}
                </li>
            </ul>
        </div>
        <div :class="reverse ? 'lg:order-1' : 'lg:order-2'">
            <slot name="visual" />
        </div>
    </div>
</template>
