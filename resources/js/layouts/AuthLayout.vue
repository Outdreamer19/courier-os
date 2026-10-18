<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import DemoBanner from '@/components/DemoBanner.vue';
import { Toaster } from '@/components/ui/sonner';
import { useTenantTheme } from '@/composables/useTenantTheme';
import AuthLoginSplitLayout from '@/layouts/auth/AuthLoginSplitLayout.vue';
import AuthSimpleLayout from '@/layouts/auth/AuthSimpleLayout.vue';

const { title = '', description = '' } = defineProps<{
    title?: string;
    description?: string;
}>();

// Login and Register share the split photo/form treatment; every other
// auth page (forgot/reset password, two-factor, passkey, ...) keeps the
// existing simple centered layout.
const SPLIT_LAYOUT_PAGES = ['auth/Login', 'auth/Register'];

const page = usePage();

// Sign in pages wear the courier's colours too.
useTenantTheme();
const layout = computed(() =>
    SPLIT_LAYOUT_PAGES.includes(page.component)
        ? AuthLoginSplitLayout
        : AuthSimpleLayout,
);
</script>

<template>
    <DemoBanner />
    <Toaster richColors position="top-center" />
    <component :is="layout" :title="title" :description="description">
        <slot />
    </component>
</template>
