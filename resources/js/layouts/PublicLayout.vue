<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, MessageCircle, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import DemoBanner from '@/components/DemoBanner.vue';
import { Toaster } from '@/components/ui/sonner';
import {
    home,
    about,
    rates,
    contact,
    dashboard,
    login,
    register,
} from '@/routes';
import { terms, privacy, shipping, refund, restricted } from '@/routes/legal';
import type { BrandConfig, WarehouseSnapshot } from '@/types/auth';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const brand = computed(() => (page.props.brand as BrandConfig | undefined) ?? null);
const brandName = computed(() => brand.value?.name ?? 'CourierOS');
const warehouse = computed(
    () => (page.props.warehouse as WarehouseSnapshot | null) ?? null,
);
const whatsappUrl = computed(() => brand.value?.whatsapp_url ?? null);

const navLinks = [
    { name: 'Rates', href: rates() },
    { name: 'About', href: about() },
    { name: 'Contact', href: contact() },
];

const legalLinks = [
    { name: 'Terms', href: terms() },
    { name: 'Privacy', href: privacy() },
    { name: 'Shipping policy', href: shipping() },
    { name: 'Refunds and claims', href: refund() },
    { name: 'Restricted items', href: restricted() },
];

const mobileOpen = ref(false);

/**
 * The public site wears the courier's own colours.
 *
 * Every public page styles itself with the brand-* tokens. Rescoping those
 * tokens here, from the tenant's primary and accent, means each courier's
 * site (and the demo) carries its own palette without any page naming a
 * colour. Darker and lighter steps are mixed from the two picks so any
 * reasonable choice still produces a coherent set.
 */
const HEX = /^#[0-9A-Fa-f]{6}$/;
const FALLBACK_PRIMARY = '#1B6E8C';
const FALLBACK_ACCENT = '#F5A524';

function readableOn(hex: string): string {
    const c = (i: number) => parseInt(hex.slice(i, i + 2), 16) / 255;
    const lin = (v: number) =>
        v <= 0.03928 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4;
    const l = 0.2126 * lin(c(1)) + 0.7152 * lin(c(3)) + 0.0722 * lin(c(5));

    return l > 0.4 ? '#111418' : '#ffffff';
}

const siteTheme = computed(() => {
    const primary = HEX.test(brand.value?.primary_color ?? '')
        ? (brand.value!.primary_color as string)
        : FALLBACK_PRIMARY;
    const accent = HEX.test(brand.value?.accent_color ?? '')
        ? (brand.value!.accent_color as string)
        : FALLBACK_ACCENT;

    return {
        '--site-primary': primary,
        '--site-accent': accent,
        '--site-accent-on': readableOn(accent),
        '--brand-ink': `color-mix(in oklab, ${primary} 40%, #04080d)`,
        '--brand-ink-soft': `color-mix(in oklab, ${primary} 58%, #04080d)`,
        '--brand-green': primary,
        '--brand-green-soft': `color-mix(in oklab, ${primary} 70%, white)`,
        '--brand-green-muted': `color-mix(in oklab, ${primary} 7%, white)`,
        '--brand-gold': accent,
        '--brand-gold-soft': `color-mix(in oklab, ${accent} 70%, white)`,
        '--primary': primary,
        '--primary-foreground': readableOn(primary),
        '--ring': primary,
    } as Record<string, string>;
});
</script>

<template>
    <div
        class="flex min-h-svh flex-col bg-background text-foreground"
        :style="siteTheme"
    >
        <Toaster richColors position="top-right" />

        <DemoBanner />

        <header
            class="sticky top-0 z-40 border-b border-border/70 bg-background/90 backdrop-blur-md"
        >
            <div
                class="mx-auto flex h-[72px] max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8"
            >
                <Link
                    :href="home()"
                    class="group/logo flex items-center"
                    :aria-label="`${brandName} home`"
                >
                    <AppLogo />
                </Link>

                <nav class="hidden items-center gap-1 md:flex">
                    <Link
                        v-for="link in navLinks"
                        :key="link.name"
                        :href="link.href"
                        class="rounded-full px-4 py-2 text-[15px] font-medium text-foreground/70 transition hover:bg-brand-green-muted hover:text-foreground"
                    >
                        {{ link.name }}
                    </Link>
                </nav>

                <div class="hidden items-center gap-2 md:flex">
                    <Link
                        v-if="user"
                        :href="dashboard()"
                        class="site-btn-accent inline-flex h-10 items-center rounded-full px-5 text-[15px] font-semibold"
                    >
                        My account
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="inline-flex h-10 items-center rounded-full px-4 text-[15px] font-medium text-foreground/80 hover:text-foreground"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="register()"
                            class="inline-flex h-10 items-center rounded-full bg-brand-ink px-5 text-[15px] font-semibold text-white transition hover:bg-brand-ink-soft"
                        >
                            Sign up free
                        </Link>
                    </template>
                </div>

                <button
                    type="button"
                    class="inline-flex size-10 items-center justify-center rounded-full border border-border md:hidden"
                    :aria-expanded="mobileOpen"
                    aria-label="Toggle menu"
                    @click="mobileOpen = !mobileOpen"
                >
                    <Menu v-if="!mobileOpen" class="size-5" />
                    <X v-else class="size-5" />
                </button>
            </div>

            <div v-if="mobileOpen" class="border-t border-border md:hidden">
                <div class="mx-auto max-w-7xl space-y-1 px-4 py-4">
                    <Link
                        v-for="link in navLinks"
                        :key="link.name"
                        :href="link.href"
                        class="block rounded-xl px-3 py-2.5 text-base font-medium text-foreground/80 hover:bg-brand-green-muted"
                        @click="mobileOpen = false"
                    >
                        {{ link.name }}
                    </Link>
                    <div class="flex gap-2 pt-3">
                        <Link
                            v-if="user"
                            :href="dashboard()"
                            class="site-btn-accent flex h-11 flex-1 items-center justify-center rounded-full font-semibold"
                        >
                            My account
                        </Link>
                        <template v-else>
                            <Link
                                :href="login()"
                                class="flex h-11 flex-1 items-center justify-center rounded-full border border-border font-medium"
                            >
                                Log in
                            </Link>
                            <Link
                                :href="register()"
                                class="flex h-11 flex-1 items-center justify-center rounded-full bg-brand-ink font-semibold text-white"
                            >
                                Sign up free
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="bg-brand-ink text-white">
            <div
                class="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 md:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1fr] lg:px-8"
            >
                <div class="max-w-sm space-y-4">
                    <p class="text-xl font-bold tracking-tight">
                        {{ brandName }}
                    </p>
                    <p class="leading-relaxed text-white/65">
                        Your own US shipping address. Shop any US store and we
                        bring it home, with every step tracked in your account.
                    </p>
                    <a
                        v-if="whatsappUrl"
                        :href="whatsappUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-2 text-sm font-semibold transition hover:bg-white/10"
                    >
                        <MessageCircle class="size-4" />
                        Chat on WhatsApp
                    </a>
                </div>

                <div>
                    <p class="mb-4 text-sm font-semibold text-white">Explore</p>
                    <ul class="space-y-2.5 text-sm">
                        <li v-for="link in navLinks" :key="link.name">
                            <Link
                                :href="link.href"
                                class="text-white/65 transition hover:text-white"
                            >
                                {{ link.name }}
                            </Link>
                        </li>
                        <li>
                            <Link
                                :href="login()"
                                class="text-white/65 transition hover:text-white"
                            >
                                Log in
                            </Link>
                        </li>
                    </ul>
                </div>

                <div>
                    <p class="mb-4 text-sm font-semibold text-white">Policies</p>
                    <ul class="space-y-2.5 text-sm">
                        <li v-for="link in legalLinks" :key="link.name">
                            <Link
                                :href="link.href"
                                class="text-white/65 transition hover:text-white"
                            >
                                {{ link.name }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <div v-if="warehouse">
                    <p class="mb-4 text-sm font-semibold text-white">
                        US warehouse
                    </p>
                    <address
                        class="space-y-1 text-sm leading-relaxed text-white/65 not-italic"
                    >
                        <p>{{ warehouse.address_line_1 }}</p>
                        <p>
                            {{ warehouse.city }}, {{ warehouse.state }}
                            {{ warehouse.zip }}
                        </p>
                        <p v-if="warehouse.phone">{{ warehouse.phone }}</p>
                    </address>
                </div>
            </div>
            <div class="border-t border-white/10">
                <div
                    class="mx-auto max-w-7xl px-4 py-6 text-sm text-white/50 sm:px-6 lg:px-8"
                >
                    © {{ new Date().getFullYear() }} {{ brandName }}. All
                    rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>
