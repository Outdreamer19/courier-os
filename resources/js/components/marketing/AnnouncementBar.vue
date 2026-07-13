<script setup lang="ts">
defineProps<{ text: string; href: string }>();
</script>

<template>
    <div class="bg-marketing-periwinkle px-4 py-2.5 text-center text-sm font-medium text-marketing-ink">
        <a :href="href" class="underline-offset-2 hover:underline">{{ text }}</a>
    </div>
</template>
