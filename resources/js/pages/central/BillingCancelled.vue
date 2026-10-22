<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

defineOptions({ inheritAttrs: false });

defineProps<{
    /** Set when we know which unpaid courier this was, to go straight back. */
    finishUrl?: string | null;
}>();
</script>

<template>
    <Head title="Checkout cancelled" />

    <div
        class="courieros-brand flex min-h-screen items-center justify-center bg-marketing-cream px-6 py-12 font-marketing text-marketing-ink"
    >
        <div class="w-full max-w-lg text-center">
            <h1 class="text-2xl font-semibold tracking-tight">
                No payment was taken
            </h1>
            <p class="mt-3 text-base leading-relaxed text-muted-foreground">
                You stopped before finishing checkout, so nothing was charged.
                Your details are saved, so pick up where you left off whenever
                you're ready.
            </p>

            <div class="mt-8">
                <a :href="finishUrl ?? '/signup'">
                    <Button
                        class="h-11 bg-marketing-ink px-8 text-marketing-cream hover:bg-marketing-ink/90"
                    >
                        {{ finishUrl ? 'Finish payment' : 'Return to signup' }}
                    </Button>
                </a>
            </div>
        </div>
    </div>
</template>
