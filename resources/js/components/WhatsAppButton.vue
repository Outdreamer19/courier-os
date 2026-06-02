<script setup lang="ts">
import { MessageCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

defineProps<{
    url: string | null;
    label?: string;
}>();
</script>

<template>
    <Button
        v-if="url"
        as-child
        variant="outline"
        class="border-brand-green/40 text-brand-green hover:bg-brand-green/10"
    >
        <a :href="url" target="_blank" rel="noopener noreferrer">
            <MessageCircle class="size-4" />
            {{ label ?? 'Message on WhatsApp' }}
        </a>
    </Button>
</template>
