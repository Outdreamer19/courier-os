<script setup lang="ts">
const integrations = [
    'Stripe', 'WhatsApp Business', 'Resend', 'QuickBooks', 'Zapier', 'Google Sheets', 'Slack', 'Mailchimp',
];
</script>

<template>
    <section id="integrations" class="bg-marketing-eggshell/40 py-20 fade-in-section">
        <div class="mx-auto max-w-6xl px-4 text-center sm:px-6 lg:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-marketing-orange">Integrations</p>
            <h2 class="mt-2 text-balance text-3xl font-semibold tracking-tight text-marketing-ink sm:text-4xl">
                Works with the tools you already use
            </h2>
            <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div
                    v-for="name in integrations"
                    :key="name"
                    class="rounded-xl border border-marketing-border bg-white px-4 py-6 text-sm font-medium text-marketing-ink shadow-sm transition hover:-translate-y-1 hover:shadow-md fade-in-item"
                >
                    {{ name }}
                </div>
            </div>
        </div>
    </section>
</template>
