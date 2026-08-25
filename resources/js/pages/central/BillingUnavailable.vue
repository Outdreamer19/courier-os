<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

defineOptions({ inheritAttrs: false });

defineProps<{
    tenant: {
        name: string;
    };
}>();
</script>

<template>
    <Head title="Checkout is unavailable right now" />

    <div
        class="flex min-h-screen items-center justify-center bg-[hsl(40_30%_99%)] px-6 py-12 text-[hsl(222_47%_11%)]"
    >
        <div class="w-full max-w-lg text-center">
            <h1 class="text-2xl font-semibold tracking-tight">
                We couldn't start checkout
            </h1>
            <p class="mt-3 text-base leading-relaxed text-muted-foreground">
                This one's on us, not you — {{ tenant.name }}'s details are
                saved, but payment isn't accepting new signups at the moment.
                We've logged the problem and will follow up as soon as it's
                sorted. No charge was made.
            </p>

            <div class="mt-8 flex flex-col items-center gap-3">
                <a href="/signup">
                    <Button
                        class="h-11 bg-[hsl(168_76%_38%)] px-8 text-white hover:bg-[hsl(168_76%_32%)]"
                    >
                        Try again later
                    </Button>
                </a>
                <a
                    href="mailto:hello@courieros.co"
                    class="text-sm text-muted-foreground underline underline-offset-4 hover:text-foreground"
                >
                    Or email hello@courieros.co and we'll set you up by hand
                </a>
            </div>
        </div>
    </div>
</template>
