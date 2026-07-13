<script setup lang="ts">
import { ImageIcon } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        src?: string;
        alt: string;
        label: string;
        aspect?: string;
    }>(),
    { aspect: 'aspect-[16/10]' },
);
</script>

<template>
    <div
        class="overflow-hidden rounded-2xl border border-marketing-border bg-white shadow-[0_20px_60px_-25px_rgba(33,13,2,0.35)]"
    >
        <div
            class="flex items-center gap-1.5 border-b border-marketing-border bg-marketing-eggshell/60 px-4 py-2.5"
        >
            <span class="size-2.5 rounded-full bg-marketing-orange/50" />
            <span class="size-2.5 rounded-full bg-marketing-amber/60" />
            <span class="size-2.5 rounded-full bg-marketing-olive/40" />
        </div>
        <div :class="['relative w-full', aspect]">
            <img
                v-if="src"
                :src="src"
                :alt="alt"
                loading="lazy"
                decoding="async"
                class="absolute inset-0 h-full w-full object-cover object-top"
            />
            <div
                v-else
                class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-marketing-eggshell to-marketing-cream text-marketing-ink-muted"
            >
                <ImageIcon class="size-8" aria-hidden="true" />
                <p class="text-sm font-medium">{{ label }}</p>
                <p class="text-xs">Screenshot coming soon</p>
            </div>
        </div>
    </div>
</template>
