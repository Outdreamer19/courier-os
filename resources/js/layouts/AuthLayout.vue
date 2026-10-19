<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import DemoBanner from '@/components/DemoBanner.vue';
import { Toaster } from '@/components/ui/sonner';
import { useTenantTheme } from '@/composables/useTenantTheme';
import AuthLoginSplitLayout from '@/layouts/auth/AuthLoginSplitLayout.vue';
import AuthSimpleLayout from '@/layouts/auth/AuthSimpleLayout.vue';

const props = defineProps<{
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
// The demo courier's login page has no form (see auth/Login.vue), so the
// default "Enter your email and password" heading would be wrong there.
const isDemoLogin = computed(
    () => Boolean(page.props.demo) && page.component === 'auth/Login',
);
const title = computed(() =>
    isDemoLogin.value ? 'Explore the live demo' : (props.title ?? ''),
);
const description = computed(() =>
    isDemoLogin.value
        ? 'See exactly what your customers and your team would use.'
        : (props.description ?? ''),
);

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
