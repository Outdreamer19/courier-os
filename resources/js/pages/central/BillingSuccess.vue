<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

defineProps<{
    tenant: { name: string; subdomain: string; portal_url: string };
}>();
</script>

<template>
    <Head title="You're all set" />

    <div
        class="courieros-brand flex min-h-screen items-center justify-center bg-marketing-ink px-6 py-12 font-marketing text-white"
    >
        <div class="w-full max-w-lg text-center">
            <div
                class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-marketing-orange/15"
            >
                <svg
                    class="h-8 w-8 text-marketing-orange"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M20 6 9 17l-5-5" />
                </svg>
            </div>

            <h1 class="text-3xl font-semibold tracking-tight">
                Welcome aboard, {{ tenant.name }}
            </h1>
            <p class="mt-4 text-base leading-relaxed text-white/70">
                Your courier platform is ready. Sign in to set up your warehouse
                address, shipping rates and branding, then invite your first
                customers.
            </p>

            <div class="mt-8">
                <a :href="tenant.portal_url">
                    <Button
                        class="h-11 bg-marketing-amber px-8 font-semibold text-marketing-ink hover:bg-marketing-amber/90"
                    >
                        Go to your dashboard
                    </Button>
                </a>
            </div>

            <p class="mt-6 text-sm text-white/50">
                Your site:
                <span class="font-medium text-white/80">{{
                    tenant.portal_url
                }}</span>
            </p>
        </div>
    </div>
</template>
