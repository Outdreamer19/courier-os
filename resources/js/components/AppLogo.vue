<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const brand = computed(() => page.props.brand);

// Build initials from the business name as a logo fallback.
const initials = computed(() => {
    const name = brand.value?.name ?? 'CourierOS';
    return name
        .split(' ')
        .map((w) => w[0])
        .filter(Boolean)
        .slice(0, 2)
        .join('')
        .toUpperCase();
});

const primary = computed(() => brand.value?.primary_color || '#0f9d8f');
</script>

<template>
    <!-- Uploaded logo, if the tenant has set one -->
    <img
        v-if="brand?.logo_path"
        :src="brand.logo_path"
        :alt="brand.name"
        class="h-10 w-auto max-w-[170px] object-contain"
    />

    <!-- Otherwise a clean initials mark + the business name -->
    <div v-else class="flex items-center gap-2.5">
        <div
            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-sm font-bold text-white"
            :style="{ backgroundColor: primary }"
        >
            {{ initials }}
        </div>
        <span class="truncate text-base font-semibold tracking-tight">
            {{ brand?.name ?? 'CourierOS' }}
        </span>
    </div>
</template>
