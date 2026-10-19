<script setup lang="ts">
import {
    CalendarX2,
    CreditCard,
    DatabaseZap,
    LockKeyhole,
    MessageCircleHeart,
} from 'lucide-vue-next';

/**
 * Plain, checkable promises rather than badges or star ratings: every line
 * here is something a courier can verify the day they sign up.
 */
const items = [
    {
        icon: CreditCard,
        title: 'Secure checkout',
        body: 'Card payments handled by Stripe',
    },
    {
        icon: LockKeyhole,
        title: 'Secure website',
        body: 'The padlock on every page and login',
    },
    {
        icon: DatabaseZap,
        title: 'Your data stays yours',
        body: 'You own every customer record',
    },
    {
        icon: CalendarX2,
        title: 'No contract',
        body: 'Monthly plan, cancel any time',
    },
    {
        icon: MessageCircleHeart,
        title: 'Real people on WhatsApp',
        body: 'We set it up with you, step by step',
    },
];
</script>

<template>
    <section
        aria-label="Why couriers trust CourierOS"
        class="border-y border-marketing-border bg-white/70"
    >
        <ul
            class="mx-auto grid max-w-6xl grid-cols-1 gap-x-6 gap-y-5 px-4 py-7 sm:grid-cols-2 sm:px-6 lg:grid-cols-5 lg:px-8"
        >
            <li
                v-for="(item, index) in items"
                :key="item.title"
                class="group flex items-start gap-3"
            >
                <span
                    class="trust-icon flex size-10 shrink-0 items-center justify-center rounded-xl bg-marketing-amber/25 text-marketing-ink ring-1 ring-marketing-amber/40 transition duration-300 group-hover:-translate-y-0.5 group-hover:bg-marketing-amber"
                    :style="{ animationDelay: `${index * 0.35}s` }"
                >
                    <component :is="item.icon" class="size-5" />
                </span>
                <span class="min-w-0">
                    <span
                        class="block text-sm font-semibold text-marketing-ink"
                        >{{ item.title }}</span
                    >
                    <span class="block text-[13px] text-marketing-ink-muted">{{
                        item.body
                    }}</span>
                </span>
            </li>
        </ul>
    </section>
</template>

<style scoped>
@keyframes trust-breathe {
    0%,
    100% {
        box-shadow: 0 0 0 0 rgba(252, 213, 25, 0);
    }
    50% {
        box-shadow: 0 0 0 6px rgba(252, 213, 25, 0.18);
    }
}

.trust-icon {
    animation: trust-breathe 4s ease-in-out infinite;
}

@media (prefers-reduced-motion: reduce) {
    .trust-icon {
        animation: none;
    }
}
</style>
