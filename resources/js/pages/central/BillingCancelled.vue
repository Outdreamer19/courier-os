<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

defineOptions({ inheritAttrs: false });
</script>

<template>
    <Head title="Checkout cancelled" />

    <div
        class="flex min-h-screen items-center justify-center bg-[hsl(40_30%_99%)] px-6 py-12 text-[hsl(222_47%_11%)]"
    >
        <div class="w-full max-w-lg text-center">
            <h1 class="text-2xl font-semibold tracking-tight">
                No payment was taken
            </h1>
            <p class="mt-3 text-base leading-relaxed text-muted-foreground">
                You stopped before finishing checkout, so nothing was charged.
                Your details are saved — pick up where you left off whenever
                you're ready.
            </p>

            <div class="mt-8">
                <a href="/signup">
                    <Button
                        class="h-11 bg-[hsl(168_76%_38%)] px-8 text-white hover:bg-[hsl(168_76%_32%)]"
                    >
                        Return to signup
                    </Button>
                </a>
            </div>
        </div>
    </div>
</template>
