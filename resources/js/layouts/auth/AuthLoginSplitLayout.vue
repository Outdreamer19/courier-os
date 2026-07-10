<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh w-full items-center justify-center overflow-hidden bg-brand-cream p-4 md:p-10"
    >
        <!-- soft dot texture behind the card, echoes the marketing site's pattern language -->
        <div
            class="pointer-events-none absolute inset-0 text-primary opacity-[0.08]"
            style="background-image: radial-gradient(currentColor 1.5px, transparent 1.5px); background-size: 26px 26px"
        />

        <div
            class="relative grid w-full max-w-5xl overflow-hidden rounded-[28px] bg-card shadow-xl ring-1 ring-primary/5 md:grid-cols-2"
        >
            <!-- Left: brand / photo panel -->
            <div class="relative hidden min-h-[560px] md:block">
                <img
                    src="https://images.unsplash.com/photo-1633174524827-db00a6b7bc74?q=80&w=1200&auto=format&fit=crop"
                    alt="Package delivered to a doorstep at home"
                    class="absolute inset-0 h-full w-full object-cover"
                />
                <div
                    class="absolute inset-0 bg-gradient-to-t from-brand-ink via-brand-ink/50 to-brand-ink/75"
                />

                <div
                    class="relative flex h-full flex-col justify-between p-10 text-white"
                >
                    <Link :href="home()" class="flex items-center gap-3">
                        <span
                            class="flex size-11 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/30 backdrop-blur-sm"
                        >
                            <AppLogoIcon class="size-6" />
                        </span>
                        <span class="text-lg font-semibold tracking-tight">
                            TODAY <span class="text-brand-gold">Shipping</span>
                        </span>
                    </Link>

                    <div class="space-y-4">
                        <svg
                            viewBox="0 0 120 40"
                            class="h-7 w-auto text-brand-gold/90"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="M4 30 C40 30 70 30 108 12"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-dasharray="5 6"
                                stroke-linecap="round"
                            />
                            <path d="M108 12 L96 10 L104 18 Z" fill="currentColor" />
                        </svg>
                        <p class="text-2xl leading-snug font-semibold">
                            Shop the world.<br />
                            We'll bring it home.
                        </p>
                        <p class="text-sm text-white/75">
                            Free US address, customs handled for you, and
                            delivery right to your door in Jamaica.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right: form panel -->
            <div class="relative flex flex-col justify-center p-8 sm:p-12">
                <svg
                    viewBox="0 0 120 40"
                    class="absolute top-8 right-8 h-6 w-auto text-accent/60"
                    fill="none"
                    aria-hidden="true"
                >
                    <path
                        d="M4 30 C40 30 70 30 108 12"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-dasharray="5 6"
                        stroke-linecap="round"
                    />
                    <path d="M108 12 L96 10 L104 18 Z" fill="currentColor" />
                </svg>

                <div class="mx-auto w-full max-w-sm space-y-8">
                    <div class="space-y-1.5 text-center md:text-left">
                        <h1
                            v-if="title"
                            class="text-3xl font-bold tracking-tight text-primary"
                        >
                            {{ title }}
                        </h1>
                        <p v-if="description" class="text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>

                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
