<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();

const page = usePage();
const brandName = computed(() => page.props?.brand?.name ?? 'CourierOS');
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
    >
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-3">
                    <Link
                        :href="home()"
                        class="group flex flex-col items-center gap-3 font-medium"
                    >
                        <span
                            class="flex size-12 items-center justify-center rounded-md bg-brand-ink text-brand-gold ring-1 ring-brand-gold/40 transition group-hover:ring-brand-gold"
                        >
                            <AppLogoIcon class="size-6" />
                        </span>
                        <span class="text-base font-semibold tracking-tight">
                            {{ brandName }}
                        </span>
                    </Link>
                    <div class="space-y-1 text-center">
                        <h1 class="text-xl font-semibold tracking-tight">
                            {{ title }}
                        </h1>
                        <p class="text-center text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
