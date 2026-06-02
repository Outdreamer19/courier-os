<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LegalPage from '@/pages/public/legal/LegalPage.vue';

const sections = [
    {
        heading: '1. Acceptance of terms',
        body: "By creating an account or using Ship'd JM services, you agree to these terms and any future updates. If you do not agree, please do not use the service.",
    },
    {
        heading: '2. Account responsibilities',
        body: 'You are responsible for keeping your login credentials secure and for ensuring the accuracy of the information you submit, including shipment details and invoices.',
    },
    {
        heading: '3. Customer reference',
        body: 'Every customer receives a unique customer reference (for example DJM-000001). The reference must be used on every shipment so packages can be matched to the correct account.',
    },
    {
        heading: '4. Use of the Florida warehouse',
        body: "The Florida warehouse address provided through Ship'd JM is for receiving customer packages only. Misuse of the address, including illegal or restricted items, will result in account suspension.",
    },
    {
        heading: '5. Limitation of liability',
        body: "Ship'd JM is not responsible for delays caused by carriers, customs authorities, or events outside our reasonable control. We will, however, use all reasonable means to resolve issues quickly.",
    },
    {
        heading: '6. Changes',
        body: 'We may update these terms from time to time. Material changes will be communicated to customers in advance via the dashboard or email.',
    },
];
</script>

<template>
    <Head title="Terms & Conditions" />

    <LegalPage
        eyebrow="Legal"
        title="Terms and Conditions"
        intro="Please review these terms before using Ship'd JM. This is placeholder content for MVP."
        :sections="sections"
    />
</template>
