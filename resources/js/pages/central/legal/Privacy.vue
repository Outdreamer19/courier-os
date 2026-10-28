<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import LegalDocument from '@/components/marketing/LegalDocument.vue';
import type { LegalSection } from '@/components/marketing/LegalDocument.vue';

const props = defineProps<{
    company: {
        name: string;
        number: string;
        address: string;
        email: string;
    };
    updated: string;
}>();

const sections = computed<LegalSection[]>(() => [
    {
        heading: '1. Who we are',
        paragraphs: [
            `CourierOS is run by ${props.company.name} (company number ${props.company.number}), ${props.company.address}. For courieros.co and for the accounts of courier businesses that subscribe, we are the data controller. Questions about privacy go to ${props.company.email}.`,
            "Each courier's own site and customer portal on CourierOS is run by that courier. For their customers' data the courier is the controller and we process it on their behalf. If you are a customer of a courier, please contact them first about your data.",
        ],
    },
    {
        heading: '2. What we collect',
        list: [
            'Account details: your name, business name, email, phone number, subdomain, branding and password (stored hashed).',
            'Enquiries: what you tell us through the contact form or WhatsApp, such as your business type, volumes and how to reach you.',
            'Billing: plan, payment status and invoices. Card details are handled by Stripe and never reach our servers.',
            'Usage and technical data: pages visited, device and browser, IP address, logs and error reports, used to run and secure the service.',
            'Advertising data: if you visit courieros.co, the Meta Pixel records pages you view and actions such as starting a signup or a WhatsApp chat (see section 5).',
        ],
    },
    {
        heading: '3. How we use it, and our legal basis',
        list: [
            'To provide CourierOS, manage your account and take payment (to perform our contract with you).',
            'To reply to enquiries and follow up on a demo or quote you asked about (our legitimate interest in responding to you).',
            'To keep the service secure, fix faults and improve it (legitimate interest).',
            'To measure and improve our advertising (your consent, through cookie and pixel choices, and our legitimate interest in reaching courier businesses).',
            'To meet legal duties such as tax and accounting records (legal obligation).',
        ],
        after: [
            "We do not sell personal data, and we do not use couriers' customer data for our own marketing.",
        ],
    },
    {
        heading: '4. Who we share it with',
        paragraphs: [
            'We use trusted providers to run CourierOS. They only process data on our instructions:',
        ],
        list: [
            'DigitalOcean and Laravel Forge: hosting and server management.',
            'Cloudflare: DNS, network security, email routing and encrypted database backups.',
            'Stripe: payments and subscription billing.',
            'Resend: sending account and notification emails.',
            'Laravel Nightwatch: error and performance monitoring (EU region).',
            'Meta: the Meta Pixel on courieros.co, and WhatsApp messages where a courier turns on WhatsApp updates.',
            'InvoiceFeed: invoice and payment syncing for couriers who connect it.',
        ],
        after: [
            'We may also share data if the law requires it, or with a buyer if our business is ever sold, under the same protections.',
        ],
    },
    {
        heading: '5. Cookies and the Meta Pixel',
        paragraphs: [
            'courieros.co uses essential cookies to keep you signed in and protect forms. It also uses the Meta Pixel to understand which ads bring courier businesses to the site and to show our ads to people likely to be interested.',
            'When you fill in a form on courieros.co, the pixel can send Meta a scrambled (hashed) copy of details such as your email and phone number so Meta can match the visit to a Meta account. Meta cannot read the original details from the hash.',
            "You can limit this through your browser settings, an ad blocker, or your ad preferences in your Facebook or Instagram account. The pixel never runs on couriers' own sites.",
        ],
    },
    {
        heading: '6. International transfers',
        paragraphs: [
            'Some of our providers process data outside the UK, including in the EU and the United States. Where they do, we rely on UK adequacy regulations or approved safeguards such as the UK International Data Transfer Agreement or Addendum.',
        ],
    },
    {
        heading: '7. How long we keep it',
        list: [
            'Account data: while your account is open, then 30 days after it closes, after which it is deleted from the live system.',
            'Enquiries that do not turn into an account: up to 2 years, unless you ask us to delete them sooner.',
            'Billing and tax records: 6 years, as UK law requires.',
            'Logs and error reports: up to 90 days.',
            'Backups: encrypted and overwritten on their normal cycle.',
        ],
    },
    {
        heading: '8. Your rights',
        paragraphs: [
            `Under UK data protection law you can ask for a copy of your data, ask us to correct or delete it, object to or restrict how we use it, ask for it in a portable format, and withdraw consent at any time. Email ${props.company.email} and we will reply within one month.`,
            "If you are unhappy with how we handle your data, you can complain to the Information Commissioner's Office at ico.org.uk, but please contact us first so we can help.",
        ],
    },
    {
        heading: '9. Security',
        paragraphs: [
            'We use encrypted connections, hashed passwords, access controls, separate data for each courier, monitoring and encrypted off-site backups. No system is perfectly secure, so please use a strong, unique password.',
        ],
    },
    {
        heading: '10. Changes',
        paragraphs: [
            'We will update this page if how we use data changes, and tell account holders by email about any significant change.',
        ],
    },
]);
</script>

<template>
    <Head title="Privacy Policy" />

    <LegalDocument
        title="Privacy Policy"
        :updated="updated"
        intro="What data CourierOS collects, why, who helps us process it, and the choices you have."
        :sections="sections"
    />
</template>
