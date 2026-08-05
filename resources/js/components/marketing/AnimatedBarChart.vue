<script setup lang="ts">
import { onMounted, ref } from 'vue';

const props = defineProps<{
    title: string;
    bars: { label: string; percent: number; color: string; note?: string }[];
}>();

const containerRef = ref<HTMLElement | null>(null);
const revealed = ref(false);

onMounted(() => {
    if (!containerRef.value) {
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    revealed.value = true;
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.4 },
    );

    observer.observe(containerRef.value);
});
</script>

<template>
    <div ref="containerRef" class="rounded-2xl border border-marketing-border bg-white p-6 shadow-[0_20px_60px_-25px_rgba(33,13,2,0.35)]">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-marketing-ink-muted">
            {{ props.title }}
        </p>
        <div class="mt-5 space-y-4">
            <div v-for="bar in props.bars" :key="bar.label">
                <div class="mb-1.5 flex items-center justify-between text-sm">
                    <span class="font-medium text-marketing-ink">{{ bar.label }}</span>
                    <span v-if="bar.note" class="text-xs font-semibold text-marketing-orange">{{ bar.note }}</span>
                </div>
                <div class="h-2.5 w-full overflow-hidden rounded-full bg-marketing-eggshell">
                    <div
                        class="h-full rounded-full transition-[width] duration-[1200ms] ease-out"
                        :style="{ width: revealed ? `${bar.percent}%` : '0%', backgroundColor: bar.color }"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
