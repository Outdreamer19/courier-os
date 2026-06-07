<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

defineProps<{
    links: PaginationLink[];
}>();
</script>

<template>
    <nav
        v-if="links.length > 3"
        class="flex flex-wrap items-center justify-center gap-1"
        aria-label="Pagination"
    >
        <template v-for="(link, index) in links" :key="index">
            <span
                v-if="!link.url"
                class="inline-flex min-w-9 items-center justify-center rounded-md px-3 py-1.5 text-sm text-muted-foreground"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                class="inline-flex min-w-9 items-center justify-center rounded-md px-3 py-1.5 text-sm transition-colors"
                :class="
                    link.active
                        ? 'bg-brand-gold text-brand-ink font-medium'
                        : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                "
                v-html="link.label"
            />
        </template>
    </nav>
</template>
