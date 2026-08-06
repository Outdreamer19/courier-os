<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useBrand } from '@/composables/useBrand';
import { useMoney } from '@/lib/money';
import LegalPage from '@/pages/public/legal/LegalPage.vue';

const { brand, name: brandName } = useBrand();
const { format } = useMoney();

const ratePerLb = computed(() => format(brand.value?.default_rate_per_lb ?? 0));

// Quoted rates come from the tenant's own configuration rather than a literal,
// and the destination is described generically: this page is served by every
// courier on the platform, not just the one it was originally written for.
const sections = computed(() => [
    {
        heading: '1. Shipping route',
        body: 'Packages are received at our overseas warehouse, processed, and forwarded to your local branch for pickup. Delivery options are not available during the MVP phase.',
    },
    {
        heading: '2. Pre-alerts',
        body: 'Customers must submit a pre-alert before the package arrives at the warehouse. The pre-alert includes the invoice/receipt, tracking number, carrier, and any relevant notes.',
    },
    {
        heading: '3. Shipping rates',
        body: `The current rate is ${ratePerLb.value} per pound on a single shipping method. Final charges are confirmed by ${brandName.value} once the package is weighed at the warehouse. Pricing may evolve into weight tiers over time.`,
    },
    {
        heading: '4. Transit and processing times',
        body: 'Typical transit times will be published once the operations launch is finalised. Until then, customers can track every status change in their dashboard and contact support if anything looks delayed.',
    },
    {
        heading: '5. Pickup',
        body: 'Packages are pickup-only for now. Customers will be notified by email (and, soon, WhatsApp) once a package is ready for pickup. The pickup location and instructions will be shown in the customer dashboard.',
    },
]);
</script>

<template>
    <Head title="Shipping Policy" />

    <LegalPage
        eyebrow="Legal"
        title="Shipping Policy"
        :intro="`How ${brandName} moves packages from our overseas warehouse to pickup. Placeholder content for MVP.`"
        :sections="sections"
    />
</template>
