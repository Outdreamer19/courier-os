<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Lays a mock out at a fixed design size and scales it to whatever width the
 * page gives it. The old screenshots were raster images squeezed into small
 * frames, which is why they read as blurry, tiny and off-brand; a mock built
 * at 1100px and scaled stays sharp and keeps its proportions on any phone.
 */
const props = defineProps<{ width: number; height: number }>();

const wrap = ref<HTMLElement | null>(null);
const scale = ref(1);
let observer: ResizeObserver | null = null;

onMounted(() => {
    const sync = () => {
        if (wrap.value) {
            scale.value = wrap.value.clientWidth / props.width;
        }
    };

    sync();
    observer = new ResizeObserver(sync);

    if (wrap.value) {
        observer.observe(wrap.value);
    }
});

onBeforeUnmount(() => observer?.disconnect());
</script>

<template>
    <div
        ref="wrap"
        class="relative w-full"
        :style="{ aspectRatio: `${width} / ${height}` }"
    >
        <div
            class="absolute top-0 left-0 origin-top-left"
            :style="{
                width: `${width}px`,
                height: `${height}px`,
                transform: `scale(${scale})`,
            }"
        >
            <slot />
        </div>
    </div>
</template>
