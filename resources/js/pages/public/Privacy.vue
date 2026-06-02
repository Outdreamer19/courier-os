<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LegalPage from '@/pages/public/legal/LegalPage.vue';

const sections = [
    {
        heading: '1. Information we collect',
        body: 'We collect the information you provide when creating an account (name, email, phone, Jamaica address), submitting pre-alerts (invoices, shipment details), and contacting us. We also store package-related operational data once your shipments begin.',
    },
    {
        heading: '2. How we use information',
        body: 'We use your information to receive, process, and notify you about your packages, to communicate with you about your account, and to improve the service. We never sell your personal data.',
    },
    {
        heading: '3. Sharing with third parties',
        body: 'We share information only with carriers, customs authorities, and payment partners as needed to deliver the service, or where required by law.',
    },
    {
        heading: '4. Data security',
        body: 'We use industry-standard security practices to protect your information. Invoice uploads are stored privately and only accessible by authenticated users with the appropriate role.',
    },
    {
        heading: '5. Your rights',
        body: 'You can review and update your profile information at any time. To request deletion of your account or data, please contact us.',
    },
];
</script>

<template>
    <Head title="Privacy Policy" />

    <LegalPage
        eyebrow="Legal"
        title="Privacy Policy"
        intro="How SHIP DJM collects, uses, and protects your information. Placeholder content for MVP."
        :sections="sections"
    />
</template>
