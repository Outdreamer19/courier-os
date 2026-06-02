<script setup lang="ts">
import { Check, Copy } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';

const props = withDefaults(
    defineProps<{
        value: string;
        label?: string;
        variant?: 'default' | 'ghost' | 'outline' | 'secondary';
        size?: 'default' | 'sm' | 'icon' | 'icon-sm';
        successMessage?: string;
    }>(),
    {
        variant: 'outline',
        size: 'sm',
        successMessage: 'Copied to clipboard',
    },
);

const copied = ref(false);

const copy = async () => {
    if (!props.value) {
        return;
    }

    try {
        await navigator.clipboard.writeText(props.value);
        copied.value = true;
        toast.success(props.successMessage);

        setTimeout(() => {
            copied.value = false;
        }, 1500);
    } catch {
        toast.error('Copy failed. Please copy manually.');
    }
};
</script>

<template>
    <Button
        type="button"
        :variant="variant"
        :size="size"
        @click="copy"
        :aria-label="label ? `Copy ${label}` : 'Copy to clipboard'"
    >
        <Check v-if="copied" class="size-4 text-brand-green" />
        <Copy v-else class="size-4" />
        <span v-if="label" class="ml-1.5 hidden sm:inline">{{ label }}</span>
    </Button>
</template>
