<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const brand = computed(() => page.props.brand);

// Build initials from the business name as a logo fallback.
const initials = computed(() => {
    const name = brand.value?.name ?? 'CourierOS';

    return name
        .split(' ')
        .map((w) => w[0])
        .filter(Boolean)
        .slice(0, 2)
        .join('')
        .toUpperCase();
});

const primary = computed(() => brand.value?.primary_color || '#0f9d8f');

/**
 * A square or near square upload is an icon, so the business name sits
 * beside it. A wide upload is already a full wordmark and stands alone.
 */
const isIconShaped = ref(true);
const onLogoLoad = (event: Event) => {
    const img = event.target as HTMLImageElement;

    if (img.naturalWidth && img.naturalHeight) {
        isIconShaped.value = img.naturalWidth / img.naturalHeight < 1.6;
    }
};

const nameParts = computed(() => {
    const words = (brand.value?.name ?? 'CourierOS').trim().split(/\s+/);

    // "Blue Mahoe Express" reads as a name plus a descriptor; keep the
    // descriptor small so the name carries the mark.
    if (words.length >= 3) {
        return { main: words.slice(0, -1).join(' '), sub: words.at(-1) ?? '' };
    }

    return { main: words.join(' '), sub: '' };
});
</script>

<template>
    <div class="flex min-w-0 items-center gap-2.5">
        <img
            v-if="brand?.logo_path"
            :src="brand.logo_path"
            :alt="brand.name"
            :class="
                isIconShaped
                    ? 'size-9 shrink-0 object-contain transition-transform duration-500 group-hover/logo:scale-110 group-hover/logo:-rotate-3'
                    : 'h-10 w-auto max-w-[170px] object-contain'
            "
            @load="onLogoLoad"
        />

        <div
            v-else
            class="flex size-9 shrink-0 items-center justify-center rounded-lg text-sm font-bold text-white"
            :style="{ backgroundColor: primary }"
        >
            {{ initials }}
        </div>

        <span
            v-if="!brand?.logo_path || isIconShaped"
            class="grid min-w-0 leading-tight"
        >
            <span
                class="truncate text-[15px] font-bold tracking-tight text-sidebar-foreground"
            >
                {{ nameParts.main }}
            </span>
            <span
                v-if="nameParts.sub"
                class="truncate text-[10px] font-semibold tracking-[0.22em] text-primary uppercase"
            >
                {{ nameParts.sub }}
            </span>
        </span>
    </div>
</template>
