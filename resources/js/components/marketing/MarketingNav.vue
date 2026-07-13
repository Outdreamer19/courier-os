<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Menu, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { login } from '@/routes';

const links = [
    { name: 'Product', href: '#product' },
    { name: 'Pricing', href: '#pricing' },
    { name: 'FAQ', href: '#faq' },
];

const mobileOpen = ref(false);
</script>

<template>
    <header class="sticky top-0 z-40 border-b border-marketing-border bg-marketing-cream/85 backdrop-blur">
        <div class="mx-auto flex h-18 max-w-7xl items-center justify-between gap-6 px-4 py-3 sm:px-6 lg:px-8">
            <a href="#top" class="flex items-center gap-2 text-lg font-semibold tracking-tight text-marketing-ink">
                <span class="flex size-8 items-center justify-center rounded-lg bg-marketing-ink text-sm font-bold text-marketing-amber">
                    C
                </span>
                CourierOS
            </a>

            <nav class="hidden items-center gap-1 md:flex">
                <a
                    v-for="link in links"
                    :key="link.name"
                    :href="link.href"
                    class="rounded-md px-3 py-2 text-sm font-medium text-marketing-ink/80 transition hover:bg-marketing-eggshell hover:text-marketing-ink"
                >
                    {{ link.name }}
                </a>
            </nav>

            <div class="hidden items-center gap-2 md:flex">
                <Link :href="login()" class="rounded-md px-3 py-2 text-sm font-medium text-marketing-ink/80 hover:text-marketing-ink">
                    Login
                </Link>
                <a
                    href="/signup"
                    class="rounded-lg bg-marketing-amber px-5 py-2.5 text-sm font-semibold text-marketing-ink transition hover:brightness-95"
                >
                    Get Started
                </a>
            </div>

            <button
                type="button"
                class="inline-flex size-9 items-center justify-center rounded-md border border-marketing-border md:hidden"
                aria-label="Toggle menu"
                @click="mobileOpen = !mobileOpen"
            >
                <Menu v-if="!mobileOpen" class="size-5" />
                <X v-else class="size-5" />
            </button>
        </div>

        <div v-if="mobileOpen" class="border-t border-marketing-border md:hidden">
            <div class="mx-auto max-w-7xl space-y-1 px-4 py-3">
                <a
                    v-for="link in links"
                    :key="link.name"
                    :href="link.href"
                    class="block rounded-md px-3 py-2 text-sm font-medium text-marketing-ink/80"
                    @click="mobileOpen = false"
                >
                    {{ link.name }}
                </a>
                <div class="flex gap-2 pt-2">
                    <Link :href="login()" class="flex-1 rounded-md border border-marketing-border px-3 py-2 text-center text-sm font-medium">
                        Login
                    </Link>
                    <a href="/signup" class="flex-1 rounded-md bg-marketing-amber px-3 py-2 text-center text-sm font-semibold text-marketing-ink">
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </header>
</template>
