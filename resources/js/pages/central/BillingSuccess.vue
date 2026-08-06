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
        class="flex min-h-screen items-center justify-center bg-[hsl(222_47%_11%)] px-6 py-12 text-white"
    >
        <div class="w-full max-w-lg text-center">
            <div
                class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-[hsl(168_76%_42%)]/15"
            >
                <svg
                    class="h-8 w-8 text-[hsl(168_76%_55%)]"
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
                        class="h-11 bg-[hsl(168_76%_38%)] px-8 text-white hover:bg-[hsl(168_76%_32%)]"
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
