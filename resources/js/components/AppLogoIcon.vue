<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();

/**
 * The mark for whoever's site this is.
 *
 * This used to hardcode /branding/today-shipping-icon.png, which meant every
 * courier on the platform — and the CourierOS platform domain itself — served
 * a login page badged as Today Shipping. It now follows the shared `brand`
 * prop, which TenantConfig resolves from the tenant on tenant hosts and falls
 * back to the app name centrally.
 *
 * Tenants without an uploaded logo get a monogram rather than a stand-in
 * image, so a new courier never briefly wears somebody else's branding.
 */
const page = usePage();

const brand = computed(() => page.props?.brand ?? null);
const name = computed(() => brand.value?.name ?? 'CourierOS');
const initial = computed(
    () => name.value.trim().charAt(0).toUpperCase() || 'C',
);
</script>

<template>
    <img
        v-if="brand?.logo_path"
        :src="brand.logo_path"
        :alt="`${name} logo`"
        :class="className"
        v-bind="$attrs"
    />
    <svg
        v-else
        viewBox="0 0 24 24"
        :class="className"
        v-bind="$attrs"
        role="img"
        :aria-label="`${name} logo`"
    >
        <text
            x="12"
            y="12"
            text-anchor="middle"
            dominant-baseline="central"
            font-size="15"
            font-weight="700"
            fill="currentColor"
        >
            {{ initial }}
        </text>
    </svg>
</template>
