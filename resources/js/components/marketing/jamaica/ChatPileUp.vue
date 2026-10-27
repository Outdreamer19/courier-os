<script setup lang="ts">
import { computed, ref } from 'vue';
import { useInViewLoop } from '@/composables/useInViewLoop';

/**
 * The pain, shown rather than described: the messages every shop-and-ship
 * operator in Jamaica gets all day, stacking up faster than one person can
 * answer them. Each one is something CourierOS answers automatically.
 */
const root = ref<HTMLElement | null>(null);

const messages = [
    {
        from: 'Kerry-Ann',
        text: 'Morning! Did my Amazon package reach yet?',
        time: '7:02',
    },
    { from: 'Dwayne', text: 'How much for a 6 lb box?', time: '7:15' },
    {
        from: 'Shauna',
        text: 'I sent the invoice yesterday, you get it?',
        time: '7:31',
    },
    { from: 'Marlon', text: "What's the Florida address again?", time: '8:04' },
    { from: 'Kerry-Ann', text: 'Hello??', time: '8:20' },
    {
        from: 'Tash',
        text: 'How much I owe you for the last two?',
        time: '8:46',
    },
];

const colors: Record<string, string> = {
    'Kerry-Ann': 'text-[#ff9f43]',
    Dwayne: 'text-[#7dd3fc]',
    Shauna: 'text-[#fcd519]',
    Marlon: 'text-[#c4b5fd]',
    Tash: 'text-[#86efac]',
};

const { step } = useInViewLoop(root, {
    steps: messages.length - 2,
    interval: 650,
    hold: 3800,
});

const unread = computed(() => 9 + step.value * 3);
</script>

<template>
    <div
        ref="root"
        class="mx-auto w-full max-w-sm overflow-hidden rounded-[28px] border border-white/10 bg-[#140801] shadow-[0_40px_80px_-40px_rgba(0,0,0,0.8)]"
        role="img"
        aria-label="A WhatsApp chat filling up with customers asking where their package is, what it costs, and what they owe"
    >
        <div class="flex items-center gap-3 border-b border-white/10 px-5 py-4">
            <span
                class="flex size-9 items-center justify-center rounded-full bg-white/10 text-sm font-semibold text-white"
            >
                <svg
                    viewBox="0 0 24 24"
                    class="size-4 fill-[#25d366]"
                    aria-hidden="true"
                >
                    <path
                        d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Z"
                    />
                </svg>
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-white">Customers</p>
                <p class="truncate text-xs text-white/50">
                    Kerry-Ann, Dwayne, Shauna, Marlon, Tash…
                </p>
            </div>
            <span
                class="ml-auto rounded-full bg-[#25d366] px-2 py-0.5 text-xs font-bold text-[#0b2e17] tabular-nums transition-all"
            >
                {{ unread }}
            </span>
        </div>

        <div class="flex h-[330px] flex-col overflow-hidden justify-end gap-2 px-4 pt-4 pb-5">
            <TransitionGroup name="bubble">
                <div
                    v-for="(m, i) in messages.slice(0, step + 3)"
                    :key="`${i}-${m.time}`"
                    class="max-w-[85%] rounded-2xl rounded-tl-md bg-white/[0.08] px-3.5 py-2"
                >
                    <p class="text-xs font-semibold" :class="colors[m.from]">
                        {{ m.from }}
                    </p>
                    <p class="text-sm leading-snug text-white/90">
                        {{ m.text }}
                        <span
                            class="ml-1.5 align-bottom text-[10px] text-white/40"
                            >{{ m.time }}</span
                        >
                    </p>
                </div>
            </TransitionGroup>
        </div>
    </div>
</template>

<style scoped>
.bubble-enter-active {
    transition:
        opacity 0.35s ease-out,
        transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
}

.bubble-enter-from {
    opacity: 0;
    transform: translateY(14px) scale(0.96);
}

.bubble-leave-active {
    display: none;
}

.bubble-move {
    transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
}
</style>
