<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BellRing,
    LockKeyhole,
    PackageCheck,
    Plane,
    ShieldCheck,
    Warehouse,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();

// Every courier on the platform shares this layout, so nothing here may name
// a particular one. Colour comes from the courier's brand (useTenantTheme in
// AuthLayout) and the wordmark from the shared `brand` prop. The photo is a
// licensed stock model (public/images/people/CREDITS.md) shown as a happy
// shopper, never as a named customer, and washed in the courier's colour so
// it reads as theirs.
const page = usePage();
const brandName = computed(() => page.props?.brand?.name ?? 'CourierOS');

const photo = computed(() =>
    page.component === 'auth/Register'
        ? {
              name: 'customer-phone',
              alt: 'A smiling woman checking her phone outdoors',
              position: '50% 20%',
          }
        : {
              name: 'customer-smile',
              alt: 'A smiling woman sitting in her car',
              position: '50% 30%',
          },
);

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
            <!-- Left: a real, smiling face washed in the courier's colour -->
            <div
                class="relative hidden min-h-[600px] overflow-hidden bg-primary md:block"
            >
                <picture>
                    <source
                        :srcset="`/images/people/${photo.name}.webp`"
                        type="image/webp"
                    />
                    <img
                        :src="`/images/people/${photo.name}.jpg`"
                        :alt="photo.alt"
                        class="login-photo absolute inset-0 size-full object-cover"
                        :style="{ objectPosition: photo.position }"
                    />
                </picture>
                <div
                    class="absolute inset-0 bg-gradient-to-t from-primary from-15% via-primary/55 via-50% to-black/25"
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
                        <span
                            class="text-xl font-bold tracking-tight drop-shadow"
                        >
                            {{ brandName }}
                        </span>
                    </Link>

                    <!-- A notification the shopper is smiling about -->
                    <div
                        class="login-float ml-auto flex max-w-[16rem] items-center gap-3 rounded-2xl bg-white/95 p-3 text-foreground shadow-2xl backdrop-blur"
                    >
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary text-primary-foreground"
                        >
                            <BellRing class="login-ring size-5" />
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold"
                                >Your package is ready</span
                            >
                            <span
                                class="block truncate text-xs text-muted-foreground"
                                >Collect it today or book delivery</span
                            >
                        </span>
                    </div>

                    <div class="space-y-5">
                        <p
                            class="text-[28px] leading-tight font-bold drop-shadow-sm"
                        >
                            Shop the world.<br />
                            We'll bring it home.
                        </p>
                        <ul class="grid gap-2.5">
                            <li
                                v-for="step in steps"
                                :key="step.label"
                                class="flex items-center gap-3 text-sm font-medium"
                            >
                                <span
                                    class="flex size-8 items-center justify-center rounded-lg bg-white/15 ring-1 ring-white/30 backdrop-blur-sm"
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

                    <!-- Reassurance, kept to things that are simply true -->
                    <ul
                        class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2 border-t border-border pt-6 text-xs text-muted-foreground md:justify-start"
                    >
                        <li class="flex items-center gap-1.5">
                            <LockKeyhole class="size-3.5 text-emerald-600" />
                            Encrypted connection
                        </li>
                        <li class="flex items-center gap-1.5">
                            <ShieldCheck class="size-3.5 text-emerald-600" />
                            Your details are never shared
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes login-float {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-8px);
    }
}

@keyframes login-ring {
    0%,
    80%,
    100% {
        transform: rotate(0);
    }
    86% {
        transform: rotate(-16deg);
    }
    92% {
        transform: rotate(12deg);
    }
}

@keyframes login-zoom {
    from {
        transform: scale(1.08);
    }
    to {
        transform: scale(1);
    }
}

.login-float {
    animation: login-float 5s ease-in-out infinite;
}

.login-ring {
    animation: login-ring 3.5s ease-in-out infinite;
}

.login-photo {
    animation: login-zoom 1.6s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@media (prefers-reduced-motion: reduce) {
    .login-float,
    .login-ring,
    .login-photo {
        animation: none;
    }
}
</style>
