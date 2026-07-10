<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LegalPage from '@/pages/public/legal/LegalPage.vue';

const sections = [
    {
        heading: '1. Shipping route',
        body: 'Packages are received at our Florida warehouse, processed, and forwarded to Jamaica for pickup. Delivery options are not available during the MVP phase.',
    },
    {
        heading: '2. Pre-alerts',
        body: 'Customers must submit a pre-alert before the package arrives at the Florida warehouse. The pre-alert includes the invoice/receipt, tracking number, carrier, and any relevant notes.',
    },
    {
        heading: '3. Shipping rates',
        body: "The current MVP rate is JMD $500 per pound on a single shipping method. Final charges are confirmed by TODAY Shipping admin once the package is weighed at the Florida warehouse. Pricing may evolve into weight tiers over time.",
    },
    {
        heading: '4. Transit and processing times',
        body: 'Typical transit times will be published once the operations launch is finalised. Until then, customers can track every status change in their dashboard and contact support if anything looks delayed.',
    },
    {
        heading: '5. Pickup',
        body: 'Packages are pickup-only for now. Customers will be notified by email (and, soon, WhatsApp) once a package is ready for pickup. The pickup location and instructions will be shown in the customer dashboard.',
    },
];
</script>

<template>
    <Head title="Shipping Policy" />

    <LegalPage
        eyebrow="Legal"
        title="Shipping Policy"
        intro="How TODAY Shipping moves packages from our Florida warehouse to pickup in Jamaica. Placeholder content for MVP."
        :sections="sections"
    />
</template>
