<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import LegalDocument from '@/components/marketing/LegalDocument.vue';
import type { LegalSection } from '@/components/marketing/LegalDocument.vue';

const props = defineProps<{
    pricing: { monthly: number; setup: number; currency: string };
    company: {
        name: string;
        number: string;
        address: string;
        email: string;
    };
    updated: string;
}>();

const money = (n: number) =>
    `${props.pricing.currency === 'USD' ? 'US$' : props.pricing.currency + ' '}${n}`;

const sections = computed<LegalSection[]>(() => [
    {
        heading: '1. Who we are',
        paragraphs: [
            `CourierOS is provided by ${props.company.name}, a company registered in England and Wales (company number ${props.company.number}), registered office ${props.company.address} ("we", "us"). You can contact us at ${props.company.email}.`,
            'These terms apply when a business ("you") signs up for CourierOS or uses it. By creating an account or paying for a plan, you agree to them on behalf of your business. CourierOS is a business service and is not sold to consumers.',
        ],
    },
    {
        heading: '2. The service',
        paragraphs: [
            'CourierOS gives your courier business a branded website on a courieros.co subdomain (or your own domain where set up), a customer portal with an overseas warehouse address, pre-alerts, package tracking, invoicing and payments, customer notifications and an admin console for your staff.',
            'We may improve, change or remove features over time. If a change materially reduces what you are paying for, we will tell you in advance and you may cancel without charge for the remainder of that billing period.',
        ],
    },
    {
        heading: '3. Your account',
        list: [
            'You must give accurate business and contact details and keep them up to date.',
            'You are responsible for everyone you invite to your admin console and for keeping logins secure. Tell us straight away if you think an account has been compromised.',
            'You must be authorised to bind the business you sign up for.',
        ],
    },
    {
        heading: '4. Fees and billing',
        paragraphs: [
            `The standard plan is ${money(props.pricing.monthly)} per month plus a one-off setup fee of ${money(props.pricing.setup)}. Any promotional setup price, such as a founding offer, is the one shown at checkout. Prices exclude any taxes that apply.`,
            'Payments are taken by card through Stripe. The setup fee and first month are charged at signup, and the plan renews automatically each month until you cancel. We do not charge per package, per customer or per staff member.',
            'If a payment fails, we will try again and contact you. If it is still unpaid after 14 days, we may suspend access until it is paid. We will give you at least 30 days notice of any price increase, which applies from your next billing period after the notice ends.',
        ],
    },
    {
        heading: '5. Cancellation',
        paragraphs: [
            'You can cancel at any time by emailing us from your account email address. Cancellation takes effect at the end of the billing period you have already paid for, and your site stays live until then. Refunds are covered in our Refunds & Cancellation policy.',
        ],
    },
    {
        heading: '6. Your data and your customers',
        paragraphs: [
            'You own the data you and your customers put into CourierOS, including customer records, packages, invoices and branding. You give us permission to host and process it only to run, secure, support and improve the service for you.',
            "For your customers' personal data, your business is the controller and we act as your processor. We process it only on your instructions as set out in these terms, keep it confidential, protect it with appropriate security, use the subprocessors listed in our Privacy Policy, help you respond to customer requests, and tell you without undue delay if we become aware of a personal data breach affecting it.",
            "You are responsible for having a lawful basis to collect your customers' data, for your own customer-facing terms and privacy notice, and for the content you publish on your site.",
            'After your account closes we keep your data for 30 days so you can ask for an export, then delete it from the live system. Encrypted backups are overwritten on their normal cycle.',
        ],
    },
    {
        heading: '7. Acceptable use',
        paragraphs: ['You must not use CourierOS to:'],
        list: [
            'ship or advertise items that are illegal to import or export, or that your carriers prohibit;',
            'send spam, or message people who have not agreed to hear from you;',
            'break the law, including customs, sanctions, data protection and consumer protection law;',
            "try to access other couriers' data, probe or overload the service, or get around its limits.",
        ],
        after: [
            'We may suspend an account that breaks these rules or puts the service or other customers at risk. Where it is reasonable, we will contact you first.',
        ],
    },
    {
        heading: '8. Availability and support',
        paragraphs: [
            'We aim to keep CourierOS available around the clock and to fix problems promptly, but we do not promise it will be uninterrupted or error free. Planned maintenance will be kept short and done at quiet times where we can. Support is by email and WhatsApp during UK business hours.',
        ],
    },
    {
        heading: '9. Liability',
        paragraphs: [
            'Nothing in these terms limits liability for death or personal injury caused by negligence, for fraud, or for anything else that cannot be limited by law.',
            'Otherwise, we are not liable for loss of profit, revenue, business or goodwill, or for any indirect or consequential loss. We are not responsible for carriers, customs authorities, payment providers or other third parties outside our control, or for packages handled by your business.',
            'Our total liability to you in any 12 month period is limited to the fees you paid us in that period.',
        ],
    },
    {
        heading: '10. Ending the agreement',
        paragraphs: [
            'Either of us may end this agreement by cancelling as described above. We may end it immediately if you seriously or repeatedly break these terms, or with 30 days notice if we stop offering CourierOS, in which case we will refund any fees paid for time after it closes.',
        ],
    },
    {
        heading: '11. Changes to these terms',
        paragraphs: [
            'We may update these terms. We will email you at least 30 days before any change that materially affects you. If you keep using CourierOS after it takes effect, the new terms apply; if you do not agree, you can cancel before then.',
        ],
    },
    {
        heading: '12. Law',
        paragraphs: [
            'These terms are governed by the law of England and Wales, and the courts of England and Wales have exclusive jurisdiction. If you have a problem, please contact us first so we can try to put it right.',
        ],
    },
]);
</script>

<template>
    <Head title="Terms of Service" />

    <LegalDocument
        title="Terms of Service"
        :updated="updated"
        intro="The agreement between your courier business and CourierOS. Plain English, no surprises."
        :sections="sections"
    />
</template>
