<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

const props = defineProps<{
    links: PaginationLink[];
}>();

type Item = PaginationLink & {
    /** 'prev' and 'next' render as arrow buttons, 'page' as a numbered pill. */
    kind: 'prev' | 'next' | 'page';
    text: string;
};

/**
 * Laravel hands back labels as raw HTML entities ("&laquo; Previous", "&hellip;").
 * The old markup pushed those through `v-html` on the Inertia <Link> component —
 * a directive Vue cannot apply to a component, so every numbered page rendered
 * as an empty box. Decoding to plain text here fixes it for every caller.
 */
const decode = (label: string): string => {
    const el = document.createElement('textarea');
    el.innerHTML = label;

    return el.value.trim();
};

const items = computed<Item[]>(() =>
    props.links.map((link, index) => ({
        ...link,
        kind:
            index === 0
                ? 'prev'
                : index === props.links.length - 1
                  ? 'next'
                  : 'page',
        text: decode(link.label),
    })),
);

const baseClass =
    'inline-flex h-8 min-w-8 items-center justify-center rounded-md border px-2 text-sm transition-colors';
</script>

<template>
    <nav
        v-if="links.length > 3"
        class="flex shrink-0 flex-wrap items-center justify-center gap-1"
        aria-label="Pagination"
    >
        <template v-for="(item, index) in items" :key="index">
            <!-- Disabled edge: the first/last page has no target to link to. -->
            <span
                v-if="!item.url"
                :class="[
                    baseClass,
                    'border-transparent text-muted-foreground/40',
                    item.kind === 'page' && 'border-none',
                ]"
                :aria-label="item.kind === 'page' ? undefined : item.text"
            >
                <ChevronLeft v-if="item.kind === 'prev'" class="size-4" />
                <ChevronRight v-else-if="item.kind === 'next'" class="size-4" />
                <template v-else>{{ item.text }}</template>
            </span>

            <Link
                v-else
                :href="item.url"
                preserve-scroll
                :aria-label="
                    item.kind === 'prev'
                        ? 'Previous page'
                        : item.kind === 'next'
                          ? 'Next page'
                          : `Page ${item.text}`
                "
                :aria-current="item.active ? 'page' : undefined"
                :class="[
                    baseClass,
                    item.active
                        ? 'border-brand-ink bg-brand-ink font-semibold text-white shadow-sm'
                        : 'border-transparent text-muted-foreground hover:border-border hover:bg-muted hover:text-foreground',
                ]"
            >
                <ChevronLeft v-if="item.kind === 'prev'" class="size-4" />
                <ChevronRight v-else-if="item.kind === 'next'" class="size-4" />
                <template v-else>{{ item.text }}</template>
            </Link>
        </template>
    </nav>
</template>
