<script setup lang="ts">
const pages = [
    { name: 'Product', href: '/product' },
    { name: 'Pricing', href: '/pricing' },
    { name: 'Jamaica', href: '/jamaica' },
    { name: 'FAQ', href: '/#faq' },
];

const utility = [
    { name: 'Privacy Policy', href: '/legal/privacy' },
    { name: 'Terms & Conditions', href: '/legal/terms' },
];
</script>

<template>
    <footer class="border-t border-marketing-border bg-marketing-cream">
        <div
            class="mx-auto grid max-w-6xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-4 lg:px-8"
        >
            <div class="space-y-3">
                <p
                    class="flex items-center gap-2 text-lg font-semibold tracking-tight text-marketing-ink"
                >
                    <span
                        class="flex size-7 items-center justify-center rounded-md bg-marketing-ink text-xs font-bold text-marketing-amber"
                        >C</span
                    >
                    CourierOS
                </p>
                <p class="text-sm text-marketing-ink-muted">
                    The platform Caribbean courier businesses run on.
                </p>
            </div>
            <div>
                <p
                    class="mb-3 text-xs font-semibold tracking-[0.18em] text-marketing-ink-muted uppercase"
                >
                    Pages
                </p>
                <ul class="space-y-2 text-sm">
                    <li v-for="link in pages" :key="link.name">
                        <a
                            :href="link.href"
                            class="text-marketing-ink-muted transition hover:text-marketing-ink"
                            >{{ link.name }}</a
                        >
                    </li>
                </ul>
            </div>
            <div>
                <p
                    class="mb-3 text-xs font-semibold tracking-[0.18em] text-marketing-ink-muted uppercase"
                >
                    Utility
                </p>
                <ul class="space-y-2 text-sm">
                    <li v-for="link in utility" :key="link.name">
                        <a
                            :href="link.href"
                            class="text-marketing-ink-muted transition hover:text-marketing-ink"
                            >{{ link.name }}</a
                        >
                    </li>
                </ul>
            </div>
            <div>
                <p
                    class="mb-3 text-xs font-semibold tracking-[0.18em] text-marketing-ink-muted uppercase"
                >
                    Get started
                </p>
                <a
                    href="/signup"
                    class="inline-block rounded-lg bg-marketing-amber px-5 py-2.5 text-sm font-semibold text-marketing-ink"
                >
                    Start Free Trial
                </a>
            </div>
        </div>
        <div class="border-t border-marketing-border">
            <div
                class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-4 text-xs text-marketing-ink-muted sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8"
            >
                <p>
                    © {{ new Date().getFullYear() }} CourierOS. All rights
                    reserved.
                </p>
                <p>
                    Developed by
                    <a
                        href="https://quantaradigital.co.uk/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-marketing-ink transition hover:text-marketing-amber focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-marketing-amber"
                    >
                        Quantara Digital
                    </a>
                </p>
            </div>
        </div>
    </footer>
</template>
