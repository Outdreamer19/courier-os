<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <img
        src="/branding/today-shipping-icon.png"
        alt="TODAY Shipping icon"
        :class="className"
        v-bind="$attrs"
    />
</template>
