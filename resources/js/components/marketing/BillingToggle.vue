<script setup lang="ts">
/**
 * Monthly / yearly switch for the pricing page.
 *
 * Rendered as a real checkbox rather than a styled `div` so it is reachable by
 * keyboard and announced by screen readers without an ARIA re-implementation;
 * the visible track and knob are drawn from the sibling `<span>`s.
 */
const yearly = defineModel<boolean>({ required: true });

defineProps<{
    /** Whole-number percentage, e.g. 20 for "save 20%". */
    discountPercent: number;
}>();
</script>

<template>
    <div class="flex flex-wrap items-center justify-center gap-3">
        <label class="group flex cursor-pointer items-center gap-3">
            <span
                class="text-sm font-medium transition-colors"
                :class="
                    yearly
                        ? 'text-marketing-ink-muted'
                        : 'text-marketing-orange'
                "
            >
                Monthly
            </span>

            <span class="relative inline-flex">
                <input
                    v-model="yearly"
                    type="checkbox"
                    role="switch"
                    class="peer size-full absolute inset-0 z-10 cursor-pointer opacity-0"
                    aria-label="Bill yearly"
                />
                <span
                    class="block h-7 w-12 rounded-full bg-marketing-eggshell ring-1 ring-marketing-border transition-colors duration-300 peer-checked:bg-marketing-ink peer-focus-visible:ring-2 peer-focus-visible:ring-marketing-orange peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-marketing-cream"
                />
                <span
                    class="pointer-events-none absolute top-1 left-1 size-5 rounded-full bg-marketing-ink shadow-sm transition-transform duration-300 ease-out peer-checked:translate-x-5 peer-checked:bg-marketing-amber"
                />
            </span>

            <span
                class="text-sm font-medium transition-colors"
                :class="
                    yearly
                        ? 'text-marketing-orange'
                        : 'text-marketing-ink-muted'
                "
            >
                Yearly
            </span>
        </label>

        <span
            class="rounded-full px-3 py-1 text-xs font-semibold tracking-wide transition-all duration-300"
            :class="
                yearly
                    ? 'bg-marketing-orange text-white'
                    : 'bg-marketing-amber/30 text-marketing-olive'
            "
        >
            SAVE {{ discountPercent }}%
        </span>
    </div>
</template>
