<script setup lang="ts">
import { useTenantTheme } from '@/composables/useTenantTheme';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();

// Themes the authenticated portal with the current tenant's brand colours.
// No-ops on central/platform pages, where no tenant is bound.
useTenantTheme();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
