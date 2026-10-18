<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Thin strip shown on every page of the public demo courier, so nobody
 * mistakes Blue Mahoe Express for a real business, and so a prospect who
 * likes what they see is one tap from getting their own.
 */
const page = usePage();
const demo = computed(() => page.props.demo ?? null);
</script>

<template>
    <div
        v-if="demo"
        class="relative z-50 w-full bg-[#14323a] text-[13px] leading-snug text-white"
        role="note"
    >
        <div
            class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-x-4 gap-y-1 px-4 py-2 text-center"
        >
            <span>
                <strong class="font-semibold"
                    >Live demo of {{ demo.platform_name }}.</strong
                >
                {{ page.props.brand?.name }} is a made up courier. Click
                anything, it resets every night.
            </span>
            <a
                :href="demo.get_started_url"
                class="inline-flex items-center rounded-full bg-[#F2A93B] px-3 py-0.5 font-semibold text-[#14323a] transition hover:bg-[#f5bb62] focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none"
            >
                Get this for your courier
            </a>
        </div>
    </div>
</template>
