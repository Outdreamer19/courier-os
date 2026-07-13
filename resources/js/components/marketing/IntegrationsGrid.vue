<script setup lang="ts">
interface Integration {
    name: string;
    live: boolean;
}

const integrations: Integration[] = [
    { name: 'Stripe', live: true },
    { name: 'WhatsApp Business', live: true },
    { name: 'Resend', live: true },
    { name: 'QuickBooks', live: false },
    { name: 'Zapier', live: false },
    { name: 'Google Sheets', live: false },
    { name: 'Slack', live: false },
    { name: 'Mailchimp', live: false },
];
</script>

<template>
    <section
        id="integrations"
        class="fade-in-section bg-marketing-eggshell/40 py-20"
    >
        <div class="mx-auto max-w-6xl px-4 text-center sm:px-6 lg:px-8">
            <p
                class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase"
            >
                Integrations
            </p>
            <h2
                class="mt-2 text-3xl font-semibold tracking-tight text-balance text-marketing-ink sm:text-4xl"
            >
                Works with the tools you already use
            </h2>
            <p class="mx-auto mt-3 max-w-md text-sm text-marketing-ink-muted">
                <span class="font-semibold text-marketing-orange-deep">✓</span>
                live today &mdash; everything else is on our roadmap.
            </p>
            <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div
                    v-for="integration in integrations"
                    :key="integration.name"
                    class="fade-in-item relative flex flex-col items-center gap-2 rounded-xl border border-marketing-border bg-white px-4 py-6 text-sm font-medium text-marketing-ink shadow-sm transition hover:-translate-y-1 hover:shadow-md"
                >
                    <span
                        v-if="integration.live"
                        class="absolute top-2 right-2 text-xs font-bold text-marketing-orange-deep"
                        title="Live integration"
                    >
                        <span aria-hidden="true">✓</span>
                        <span class="sr-only">(live today)</span>
                    </span>
                    <span>{{ integration.name }}</span>
                    <span
                        v-if="!integration.live"
                        class="rounded-full border border-marketing-border bg-marketing-eggshell px-2 py-0.5 text-[10px] font-semibold tracking-wide text-marketing-orange uppercase"
                    >
                        Coming soon
                    </span>
                </div>
            </div>
        </div>
    </section>
</template>
