<script setup lang="ts">
import { BellRing, CheckCheck, MessageCircle } from 'lucide-vue-next';

/**
 * Real faces next to the product. The photos are licensed stock models
 * (see public/images/people/CREDITS.md), so they illustrate who the product
 * is for and are never captioned as customers or testimonials. The one
 * named person is the founder.
 */
withDefaults(
    defineProps<{
        whatsappHref?: string | null;
        /** Drop a real headshot at this path to replace the initials. */
        founderPhoto?: string | null;
    }>(),
    { whatsappHref: null, founderPhoto: null },
);

const emit = defineEmits<{ (e: 'whatsapp'): void }>();
</script>

<template>
    <section class="fade-in-section py-20 sm:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p
                    class="text-sm font-semibold tracking-[0.14em] text-marketing-orange uppercase"
                >
                    People first
                </p>
                <h2
                    class="mt-3 text-3xl font-semibold tracking-[-0.03em] text-balance sm:text-4xl"
                >
                    Made for the people behind every parcel
                </h2>
                <p class="mt-4 text-lg text-marketing-ink-muted">
                    You run it from your phone or laptop. Your customers watch
                    their packages come home, step by step.
                </p>
            </div>

            <div class="mt-12 grid gap-6 md:grid-cols-2">
                <!-- The courier -->
                <figure
                    class="group relative overflow-hidden rounded-[28px] bg-marketing-eggshell"
                >
                    <picture>
                        <source
                            srcset="/images/people/owner-laptop.webp"
                            type="image/webp"
                        />
                        <img
                            src="/images/people/owner-laptop.jpg"
                            alt="A smiling young woman working on a laptop at a café counter"
                            loading="lazy"
                            class="aspect-[4/5] w-full object-cover object-[50%_30%] transition duration-700 group-hover:scale-[1.03]"
                        />
                    </picture>
                    <div
                        class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-black/55 to-transparent"
                        aria-hidden="true"
                    />
                    <div
                        class="people-float absolute top-5 right-5 flex max-w-[15rem] items-center gap-3 rounded-2xl bg-white/95 p-3 shadow-xl backdrop-blur"
                    >
                        <span
                            class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-marketing-amber text-marketing-ink"
                        >
                            <BellRing class="people-ring size-4" />
                        </span>
                        <span class="min-w-0 text-left">
                            <span
                                class="block text-[13px] font-semibold text-marketing-ink"
                                >New pre-alert</span
                            >
                            <span
                                class="block truncate text-xs text-marketing-ink-muted"
                                >Amazon order, invoice attached</span
                            >
                        </span>
                    </div>
                    <figcaption
                        class="absolute right-6 bottom-6 left-6 text-white"
                    >
                        <span class="text-lg font-semibold">For you</span>
                        <span class="block text-sm text-white/85">
                            Pre-alerts, packages and payments in one place, not
                            a WhatsApp group.
                        </span>
                    </figcaption>
                </figure>

                <!-- Their customers -->
                <figure
                    class="group relative overflow-hidden rounded-[28px] bg-marketing-eggshell"
                >
                    <picture>
                        <source
                            srcset="/images/people/customer-phone.webp"
                            type="image/webp"
                        />
                        <img
                            src="/images/people/customer-phone.jpg"
                            alt="A smiling woman checking her phone outdoors"
                            loading="lazy"
                            class="aspect-[4/5] w-full object-cover object-[50%_25%] transition duration-700 group-hover:scale-[1.03]"
                        />
                    </picture>
                    <div
                        class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-black/55 to-transparent"
                        aria-hidden="true"
                    />
                    <div
                        class="people-float absolute top-5 left-5 max-w-[16rem] rounded-2xl rounded-tl-sm bg-[#dcf8c6] p-3 text-left shadow-xl"
                        style="animation-delay: -2s"
                    >
                        <span
                            class="block text-[13px] leading-snug text-[#111b21]"
                        >
                            Your package has landed and is ready for pickup.
                        </span>
                        <span
                            class="mt-1 flex items-center justify-end gap-1 text-[11px] text-[#667781]"
                        >
                            09:41 <CheckCheck class="size-3.5 text-[#53bdeb]" />
                        </span>
                    </div>
                    <figcaption
                        class="absolute right-6 bottom-6 left-6 text-white"
                    >
                        <span class="text-lg font-semibold"
                            >For your customers</span
                        >
                        <span class="block text-sm text-white/85">
                            Their own Florida address, live tracking and a
                            WhatsApp update at every step.
                        </span>
                    </figcaption>
                </figure>
            </div>

            <!-- Founder -->
            <div
                class="mt-6 flex flex-col items-start gap-5 rounded-[28px] border border-marketing-border bg-white p-6 sm:flex-row sm:items-center sm:p-8"
            >
                <img
                    v-if="founderPhoto"
                    :src="founderPhoto"
                    alt="Shane Bell, founder of CourierOS"
                    class="size-16 shrink-0 rounded-full object-cover ring-4 ring-marketing-amber/40"
                />
                <span
                    v-else
                    class="flex size-16 shrink-0 items-center justify-center rounded-full bg-marketing-ink text-lg font-semibold text-white ring-4 ring-marketing-amber/40"
                    aria-hidden="true"
                >
                    SB
                </span>
                <div class="flex-1">
                    <p class="text-lg leading-relaxed text-marketing-ink">
                        "I'm from Jamaica and I built CourierOS for couriers
                        back home. I set up every new courier with you myself,
                        and you can message me on WhatsApp any time."
                    </p>
                    <p class="mt-2 text-sm text-marketing-ink-muted">
                        <span class="font-semibold text-marketing-ink"
                            >Shane Bell</span
                        >, founder of CourierOS
                    </p>
                </div>
                <a
                    v-if="whatsappHref"
                    :href="whatsappHref"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-marketing-ink px-5 py-3 text-sm font-semibold text-white transition hover:-translate-y-0.5"
                    @click="emit('whatsapp')"
                >
                    <MessageCircle class="size-4" />
                    Message Shane
                </a>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes people-float {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-8px);
    }
}

@keyframes people-ring {
    0%,
    80%,
    100% {
        transform: rotate(0);
    }
    85% {
        transform: rotate(-16deg);
    }
    90% {
        transform: rotate(14deg);
    }
    95% {
        transform: rotate(-8deg);
    }
}

.people-float {
    animation: people-float 5s ease-in-out infinite;
}

.people-ring {
    animation: people-ring 3.5s ease-in-out infinite;
}

@media (prefers-reduced-motion: reduce) {
    .people-float,
    .people-ring {
        animation: none;
    }
}
</style>
