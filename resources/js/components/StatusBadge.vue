<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    label: string;
    status: string;
}>();

const variantClass = computed(() => {
    const value = props.status.toLowerCase();

    if (
        value.includes('paid')
        || value.includes('completed')
        || value.includes('picked_up')
        || value.includes('ready')
    ) {
        return 'border-brand-green/40 bg-brand-green/10 text-brand-green';
    }

    if (
        value.includes('issue')
        || value.includes('hold')
        || value.includes('cancel')
        || value.includes('unpaid')
    ) {
        return 'border-destructive/30 bg-destructive/10 text-destructive';
    }

    if (
        value.includes('review')
        || value.includes('pending')
        || value.includes('processing')
        || value === 'new'
        || value.includes('submitted')
    ) {
        return 'border-brand-gold/40 bg-brand-gold/10 text-brand-ink';
    }

    return 'border-border bg-muted text-muted-foreground';
});
</script>

<template>
    <Badge variant="outline" :class="variantClass">
        {{ label }}
    </Badge>
</template>
