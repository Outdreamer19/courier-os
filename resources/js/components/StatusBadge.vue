<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { statusTone } from '@/lib/statusTone';

const props = defineProps<{
    label: string;
    status: string;
}>();

const tone = computed(() => statusTone(props.status));
</script>

<template>
    <Badge
        variant="outline"
        :class="[
            'gap-1.5 py-0.5 pr-2.5 pl-2 text-xs font-semibold whitespace-nowrap',
            tone.badge,
        ]"
    >
        <span :class="['size-1.5 shrink-0 rounded-full', tone.dot]" />
        {{ label }}
    </Badge>
</template>
