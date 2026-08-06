<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useBrand } from '@/composables/useBrand';
import LegalPage from '@/pages/public/legal/LegalPage.vue';

const { name: brandName } = useBrand();

const sections = computed(() => [
    {
        heading: 'About this policy',
        body: 'The full list of restricted or prohibited items is being finalised. The categories below are common examples that may not be accepted at our warehouse or in transit.',
    },
    {
        heading: 'Likely restricted categories',
        body: '• Firearms, ammunition, and weapons\n• Explosives, flammable liquids, and pressurised containers\n• Illegal drugs and controlled substances\n• Live animals and certain perishables\n• Counterfeit goods or items violating intellectual property rights\n• High-value jewellery or precious metals (additional documentation may be required)',
    },
    {
        heading: 'Customs and import rules',
        body: 'Your local customs authority has the final say on what is allowed into the country. Customers are responsible for ensuring their purchases comply with the import laws that apply to them.',
    },
    {
        heading: 'Help us update this list',
        body: `If you are unsure whether an item is allowed, please contact ${brandName.value} support before placing an order. Once a final restricted-items list is approved, it will be published here and editable by the admin team.`,
    },
]);
</script>

<template>
    <Head title="Restricted Items" />

    <LegalPage
        eyebrow="Legal"
        title="Restricted Items Policy"
        intro="Some items may not be accepted at our warehouse or in transit. This page is a placeholder until the official list is confirmed."
        :sections="sections"
    />
</template>
