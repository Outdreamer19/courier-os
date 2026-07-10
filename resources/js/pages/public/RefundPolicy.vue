<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LegalPage from '@/pages/public/legal/LegalPage.vue';

const sections = [
    {
        heading: '1. Damage and loss',
        body: "If a package arrives damaged or appears to be missing, please contact TODAY Shipping support as soon as possible. We will investigate with the relevant carriers and customs authorities.",
    },
    {
        heading: '2. Refunds and claims',
        body: "Refunds for TODAY Shipping shipping fees may be issued at our discretion, depending on the cause of the issue and the supporting documentation provided. Original merchant refunds (the store you bought from) must be requested directly with that merchant.",
    },
    {
        heading: '3. Customs and duties',
        body: "Customers are responsible for any customs duties or fees imposed by the Jamaica Customs Agency. TODAY Shipping may collect these on behalf of customs where required.",
    },
    {
        heading: '4. Disputes',
        body: 'If you disagree with a charge or amount due, contact us through the support form or your customer dashboard. We aim to resolve disputes quickly and fairly.',
    },
];
</script>

<template>
    <Head title="Refund & Claims Policy" />

    <LegalPage
        eyebrow="Legal"
        title="Refund and Claims Policy"
        intro="How TODAY Shipping handles damaged, missing, or disputed packages. Placeholder content for MVP."
        :sections="sections"
    />
</template>
