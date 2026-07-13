<script setup lang="ts">
import { BadgeCheck, Bell, Calculator, LayoutDashboard, MapPinned, Package, Receipt, ShieldCheck, Users } from 'lucide-vue-next';

const features = [
    { icon: Receipt, title: 'Pre-Alert Management', body: 'Customers submit invoice and shipment details before packages ever arrive.' },
    { icon: Package, title: 'Package Tracking', body: 'A clear status timeline from warehouse intake through pickup.' },
    { icon: MapPinned, title: 'Branded Subdomain', body: 'Your business live at yourname.courieros.co in minutes — a custom domain later.' },
    { icon: BadgeCheck, title: 'Stripe Billing', body: 'Your platform subscription is billed automatically and securely.' },
    { icon: Bell, title: 'WhatsApp Notifications', body: 'Status updates sent by email and WhatsApp, where customers already are.' },
    { icon: Calculator, title: 'Rate Calculator', body: 'Transparent, weight-based pricing your customers can check themselves.' },
    { icon: LayoutDashboard, title: 'Customer Portal', body: 'A self-serve dashboard for every customer — no phone calls needed.' },
    { icon: Users, title: 'Role-Based Staff Access', body: 'Owner, admin, and staff permissions are built in from day one.' },
    { icon: ShieldCheck, title: 'Reports & Analytics', body: 'See packages, revenue, and activity at a glance.' },
];
</script>

<template>
    <section id="product" class="bg-marketing-cream py-20 fade-in-section">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-marketing-orange">Extra Features</p>
                <h2 class="mt-2 text-balance text-3xl font-semibold tracking-tight text-marketing-ink sm:text-4xl">
                    Discover Endless Opportunities
                </h2>
            </div>
            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="(feature, index) in features"
                    :key="feature.title"
                    class="rounded-2xl border border-marketing-border bg-white p-6 fade-in-item transition hover:-translate-y-1 hover:shadow-lg"
                    :style="{ '--fade-delay': `${index * 60}ms` }"
                >
                    <div class="flex size-10 items-center justify-center rounded-lg bg-marketing-orange/12 text-marketing-orange">
                        <component :is="feature.icon" class="size-5" />
                    </div>
                    <h3 class="mt-4 font-semibold text-marketing-ink">{{ feature.title }}</h3>
                    <p class="mt-1.5 text-sm text-marketing-ink-muted">{{ feature.body }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
