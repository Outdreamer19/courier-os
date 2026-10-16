<script setup lang="ts">
import { Check, Copy, FileText } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useInViewLoop } from '@/composables/useInViewLoop';
import PhoneFrame from './PhoneFrame.vue';
import ScaleToFit from './ScaleToFit.vue';

/**
 * The customer's side, on the phone they actually use: their own Miami
 * address to paste at checkout, and a pre-alert filled in and sent without a
 * single message to the courier.
 */
const root = ref<HTMLElement | null>(null);

const { step } = useInViewLoop(root, { steps: 6, interval: 900, hold: 3200 });

const copied = computed(() => step.value >= 1);
const fields = computed(() => ({
    store: step.value >= 2 ? 'Amazon' : '',
    tracking: step.value >= 3 ? 'TBA318840227001' : '',
    value: step.value >= 4 ? 'US$ 46.99' : '',
}));
const sent = computed(() => step.value >= 5);
</script>

<template>
    <div ref="root" class="mx-auto w-full max-w-[260px]">
        <ScaleToFit :width="300" :height="610">
            <div
                class="h-full w-full"
                role="img"
                aria-label="A customer's phone showing their personal Miami shipping address and a pre-alert being submitted for an Amazon order"
            >
                <PhoneFrame>
                    <div
                        class="flex h-full flex-col gap-3 bg-[#fbfaf7] px-3.5 pt-[48px] text-marketing-ink"
                    >
                        <div>
                            <p class="text-[17px] font-semibold tracking-tight">
                                Hi Shauna
                            </p>
                            <p class="text-[11px] text-marketing-ink-muted">
                                Customer no. IE-0148
                            </p>
                        </div>

                        <div
                            class="rounded-2xl bg-marketing-ink p-3.5 text-white"
                        >
                            <div class="flex items-center justify-between">
                                <p class="text-[11px] text-white/60">
                                    Your Miami address
                                </p>
                                <span
                                    class="flex items-center gap-1 rounded-md px-2 py-1 text-[10px] font-semibold transition-colors duration-300"
                                    :class="
                                        copied
                                            ? 'bg-[#25d366] text-[#0b2e17]'
                                            : 'bg-white/12 text-white'
                                    "
                                >
                                    <component
                                        :is="copied ? Check : Copy"
                                        class="size-3"
                                    />
                                    {{ copied ? 'Copied' : 'Copy' }}
                                </span>
                            </div>
                            <p class="mt-2 text-[13px] leading-snug">
                                Shauna Reid IE-0148<br />
                                1234 Logistics Way, Unit IE<br />
                                Miami, FL 33101
                            </p>
                        </div>

                        <div
                            class="relative rounded-2xl bg-white p-3.5 shadow-[0_8px_24px_-16px_rgba(33,13,2,0.3)]"
                        >
                            <p class="text-[13px] font-semibold">
                                New pre-alert
                            </p>
                            <div class="mt-2.5 space-y-2">
                                <div
                                    v-for="(label, key) in {
                                        store: 'Store',
                                        tracking: 'Tracking number',
                                        value: 'Order value',
                                    }"
                                    :key="key"
                                >
                                    <p
                                        class="text-[10px] text-marketing-ink-muted"
                                    >
                                        {{ label }}
                                    </p>
                                    <div
                                        class="mt-0.5 h-8 rounded-lg border px-2.5 text-[12px] leading-8 tabular-nums transition-colors duration-300"
                                        :class="
                                            fields[key]
                                                ? 'border-marketing-ink/25'
                                                : 'border-marketing-ink/10'
                                        "
                                    >
                                        {{ fields[key]
                                        }}<span
                                            v-if="!fields[key]"
                                            class="text-marketing-ink/25"
                                            >…</span
                                        >
                                    </div>
                                </div>
                                <div
                                    class="flex items-center gap-2 rounded-lg border border-dashed border-marketing-ink/15 px-2.5 py-2 text-[11px] text-marketing-ink-muted"
                                >
                                    <FileText class="size-3.5" />
                                    amazon-invoice.pdf
                                </div>
                            </div>
                            <div
                                class="mt-3 rounded-xl py-2.5 text-center text-[12px] font-semibold transition-colors duration-300"
                                :class="
                                    sent
                                        ? 'bg-[#25d366] text-[#0b2e17]'
                                        : 'bg-marketing-ink text-white'
                                "
                            >
                                {{
                                    sent
                                        ? 'Pre-alert received'
                                        : 'Send pre-alert'
                                }}
                            </div>
                        </div>
                    </div>
                </PhoneFrame>
            </div>
        </ScaleToFit>
    </div>
</template>
