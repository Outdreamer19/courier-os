<script setup lang="ts">
withDefaults(defineProps<{ logos?: { name: string; href?: string }[] }>(), {
    logos: () => [],
});
</script>

<template>
    <section
        v-if="logos.length"
        class="fade-in-section border-y border-marketing-border bg-white py-10"
    >
        <p
            class="mb-6 text-center text-xs font-semibold tracking-[0.2em] text-marketing-ink-muted uppercase"
        >
            Trusted by courier businesses across the Caribbean
        </p>
        <div class="overflow-hidden">
            <div class="marketing-marquee-track flex w-max items-center gap-16">
                <span
                    v-for="(logo, index) in [...logos, ...logos]"
                    :key="`${index}-${logo.name}`"
                    class="text-xl font-semibold text-marketing-ink-muted grayscale transition hover:text-marketing-ink hover:grayscale-0"
                >
                    {{ logo.name }}
                </span>
            </div>
        </div>
    </section>
</template>
