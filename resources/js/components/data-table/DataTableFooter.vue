<script setup lang="ts">
import PaginationLinks from '@/components/PaginationLinks.vue';

/**
 * The bar that sits under a table inside the same card: a plain-language range
 * on the left, page links on the right. Keeping them together stops pagination
 * from floating loose below the card the way it used to.
 */
const props = withDefaults(
    defineProps<{
        links?: Array<{ url: string | null; label: string; active: boolean }>;
        from?: number | null;
        to?: number | null;
        total?: number | null;
        /** Plural noun for the range summary, e.g. "packages". */
        noun?: string;
    }>(),
    { links: () => [], from: null, to: null, total: null, noun: 'results' },
);
</script>

<template>
    <div
        class="flex flex-col-reverse items-center justify-between gap-3 border-t bg-muted/30 px-6 py-3 sm:flex-row"
    >
        <p
            v-if="props.total !== null"
            class="text-xs text-muted-foreground tabular-nums"
        >
            Showing
            <span class="font-medium text-foreground">
                {{ props.from ?? 0 }}–{{ props.to ?? 0 }}
            </span>
            of
            <span class="font-medium text-foreground">{{ props.total }}</span>
            {{ props.noun }}
        </p>
        <span v-else />
        <PaginationLinks :links="props.links" />
    </div>
</template>
