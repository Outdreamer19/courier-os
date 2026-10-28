<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import LegalDocument from '@/components/marketing/LegalDocument.vue';
import type { LegalSection } from '@/components/marketing/LegalDocument.vue';

const props = defineProps<{
    company: { name: string; email: string };
    updated: string;
}>();

const sections = computed<LegalSection[]>(() => [
    {
        heading: '1. Cancelling your plan',
        paragraphs: [
            `You can cancel at any time by emailing ${props.company.email} from your account email address. There is no minimum term and no cancellation fee. Your plan stops renewing straight away, and your site and portal stay live until the end of the month you have already paid for.`,
        ],
    },
    {
        heading: '2. Monthly fees',
        paragraphs: [
            'Monthly fees are not refunded for partly used months. If you cancel within 7 days of your very first monthly charge and have not started taking customers on your site, we will refund that first month in full.',
        ],
    },
    {
        heading: '3. Setup fee',
        paragraphs: [
            'The setup fee covers getting your site, branding and portal ready. It is refundable in full if you cancel within 14 days of paying it and your site has not gone live. Once your site is live, or after 14 days, it is not refundable.',
        ],
    },
    {
        heading: '4. Problems on our side',
        paragraphs: [
            "If CourierOS is unavailable or not working properly for a significant time because of a fault on our side, tell us and we will credit or refund a fair share of that month's fee. If we ever stop offering CourierOS, we will refund any fees paid for time after it closes.",
        ],
    },
    {
        heading: '5. How refunds are paid',
        paragraphs: [
            'Refunds go back to the card you paid with through Stripe, usually within 5 to 10 business days depending on your bank. We do not charge a fee for refunds.',
        ],
    },
    {
        heading: '6. Your data after cancelling',
        paragraphs: [
            'We keep your data for 30 days after your account closes so you can ask for an export or change your mind, then delete it from the live system.',
        ],
    },
    {
        heading: "7. Your customers' payments",
        paragraphs: [
            "This policy covers what you pay CourierOS. Refunds of shipping charges or package claims between your business and your own customers follow your business's own policy.",
        ],
    },
]);
</script>

<template>
    <Head title="Refunds & Cancellation" />

    <LegalDocument
        title="Refunds & Cancellation"
        :updated="updated"
        intro="Cancel any time. Here is exactly what happens to your plan, fees and data when you do."
        :sections="sections"
    />
</template>
