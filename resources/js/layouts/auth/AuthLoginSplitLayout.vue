<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { MapPin, PackageCheck, Plane, Warehouse } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();

// Every courier on the platform shares this layout, so nothing here may name
// a particular one. Colour comes from the courier's brand (useTenantTheme in
// AuthLayout), the wordmark from the shared `brand` prop, and the artwork is
// drawn in code: no stock photography that another courier might also wear.
const page = usePage();
const brandName = computed(() => page.props?.brand?.name ?? 'CourierOS');

const steps = [
    { icon: Warehouse, label: 'Shop with your US address' },
    { icon: Plane, label: 'We fly it home' },
    { icon: PackageCheck, label: 'Collect or get it delivered' },
];
</script>

<template>
    <div
        class="relative flex min-h-svh w-full items-center justify-center overflow-hidden bg-background p-4 md:p-10"
    >
        <!-- Soft colour washes in the courier's own brand colours -->
        <div
            class="blob-drift pointer-events-none absolute -top-40 -left-32 size-[520px] rounded-full bg-primary/15 blur-3xl"
            aria-hidden="true"
        />
        <div
            class="blob-drift pointer-events-none absolute -right-24 -bottom-48 size-[560px] rounded-full bg-brand-accent/20 blur-3xl"
            style="animation-delay: -6s"
            aria-hidden="true"
        />

        <div
            class="relative grid w-full max-w-5xl overflow-hidden rounded-[28px] border border-border bg-card shadow-[0_40px_80px_-40px_rgba(15,23,42,0.35)] md:grid-cols-[1.05fr_1fr]"
        >
            <!-- Left: brand panel, drawn in code -->
            <div
                class="relative hidden min-h-[580px] overflow-hidden bg-primary md:block"
            >
                <div
                    class="absolute inset-0 bg-[radial-gradient(120%_80%_at_0%_0%,rgba(255,255,255,0.22),transparent_60%),radial-gradient(90%_70%_at_100%_100%,rgba(0,0,0,0.28),transparent_60%)]"
                    aria-hidden="true"
                />
                <div
                    class="absolute inset-0 opacity-[0.10]"
                    style="
                        background-image: radial-gradient(
                            white 1.2px,
                            transparent 1.2px
                        );
                        background-size: 22px 22px;
                    "
                    aria-hidden="true"
                />

                <div
                    class="relative flex h-full flex-col justify-between p-10 text-primary-foreground"
                >
                    <Link :href="home()" class="flex items-center gap-3">
                        <span
                            class="flex size-12 items-center justify-center rounded-2xl bg-white shadow-lg shadow-black/10"
                        >
                            <AppLogoIcon class="size-8" />
                        </span>
                        <span class="text-xl font-bold tracking-tight">
                            {{ brandName }}
                        </span>
                    </Link>

                    <!-- Route artwork: a plane rides the dashed line home -->
                    <div class="relative mx-auto my-6 w-full max-w-sm">
                        <svg
                            viewBox="0 0 320 150"
                            class="w-full overflow-visible"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                id="login-route"
                                d="M24 118 C 90 20, 220 10, 296 92"
                                stroke="currentColor"
                                stroke-opacity="0.55"
                                stroke-width="2.5"
                                stroke-dasharray="6 8"
                                stroke-linecap="round"
                                class="route-dash"
                            />
                            <circle cx="24" cy="118" r="7" fill="white" />
                            <circle
                                cx="24"
                                cy="118"
                                r="13"
                                stroke="white"
                                stroke-opacity="0.35"
                                stroke-width="2"
                            />
                            <circle
                                cx="296"
                                cy="92"
                                r="7"
                                class="fill-brand-accent"
                            />
                            <circle
                                cx="296"
                                cy="92"
                                r="14"
                                class="stroke-brand-accent"
                                stroke-opacity="0.45"
                                stroke-width="2"
                            >
                                <animate
                                    attributeName="r"
                                    values="10;18;10"
                                    dur="2.4s"
                                    repeatCount="indefinite"
                                />
                                <animate
                                    attributeName="stroke-opacity"
                                    values="0.6;0;0.6"
                                    dur="2.4s"
                                    repeatCount="indefinite"
                                />
                            </circle>
                            <g>
                                <animateMotion
                                    dur="5.5s"
                                    repeatCount="indefinite"
                                    rotate="auto"
                                    keyPoints="0;1"
                                    keyTimes="0;1"
                                    calcMode="spline"
                                    keySplines="0.45 0 0.55 1"
                                >
                                    <mpath href="#login-route" />
                                </animateMotion>
                                <circle r="15" fill="white" />
                                <path
                                    d="M-7 0 L7 0 M7 0 L2 -5 M7 0 L2 5"
                                    class="stroke-primary"
                                    stroke-width="2.4"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </g>
                        </svg>
                        <div
                            class="mt-1 flex justify-between text-[11px] font-semibold tracking-[0.18em] uppercase opacity-80"
                        >
                            <span>US warehouse</span>
                            <span class="flex items-center gap-1">
                                <MapPin class="size-3.5" /> Home
                            </span>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <p class="text-[28px] leading-tight font-bold">
                            Shop the world.<br />
                            We'll bring it home.
                        </p>
                        <ul class="grid gap-2.5">
                            <li
                                v-for="step in steps"
                                :key="step.label"
                                class="flex items-center gap-3 text-sm font-medium opacity-90"
                            >
                                <span
                                    class="flex size-8 items-center justify-center rounded-lg bg-white/15 ring-1 ring-white/25"
                                >
                                    <component :is="step.icon" class="size-4" />
                                </span>
                                {{ step.label }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Right: form panel -->
            <div class="relative flex flex-col justify-center p-8 sm:p-12">
                <!-- Brand mark for phones, where the left panel is hidden -->
                <Link
                    :href="home()"
                    class="mb-8 flex items-center justify-center gap-2.5 md:hidden"
                >
                    <AppLogoIcon class="size-9" />
                    <span
                        class="text-lg font-bold tracking-tight text-foreground"
                    >
                        {{ brandName }}
                    </span>
                </Link>

                <div class="mx-auto w-full max-w-sm space-y-8">
                    <div class="space-y-1.5 text-center md:text-left">
                        <h1
                            v-if="title"
                            class="text-3xl font-bold tracking-tight text-foreground"
                        >
                            {{ title }}
                        </h1>
                        <p
                            v-if="description"
                            class="text-sm text-muted-foreground"
                        >
                            {{ description }}
                        </p>
                    </div>

                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
