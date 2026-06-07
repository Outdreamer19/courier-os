<script setup lang="ts">
import { FileText, Upload, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';

const ACCEPT = '.pdf,.jpg,.jpeg,.png,.webp';
const ACCEPTED_TYPES = [
    'application/pdf',
    'image/jpeg',
    'image/png',
    'image/webp',
];

const model = defineModel<File | null>({ default: null });

const props = defineProps<{
    error?: string | null;
}>();

const isDragging = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const localError = ref<string | null>(null);

const formatFileSize = (bytes: number) => {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    if (bytes < 1024 * 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};

const isAcceptedFile = (file: File) => {
    const extension = file.name.split('.').pop()?.toLowerCase();

    return (
        ACCEPTED_TYPES.includes(file.type)
        || ['pdf', 'jpg', 'jpeg', 'png', 'webp'].includes(extension ?? '')
    );
};

const setFile = (file: File | null) => {
    localError.value = null;

    if (!file) {
        model.value = null;

        return;
    }

    if (!isAcceptedFile(file)) {
        localError.value = 'Please upload a PDF, JPG, PNG, or WEBP file.';

        return;
    }

    model.value = file;
};

const onDragEnter = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = true;
};

const onDragOver = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = true;
};

const onDragLeave = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = false;
};

const onDrop = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = false;

    const file = event.dataTransfer?.files?.[0] ?? null;
    setFile(file);
};

const onFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    setFile(target.files?.[0] ?? null);
};

const openFilePicker = () => {
    fileInput.value?.click();
};

const clearFile = () => {
    setFile(null);

    if (fileInput.value) {
        fileInput.value.value = '';
    }
};
</script>

<template>
    <div class="grid gap-2">
        <input
            ref="fileInput"
            type="file"
            class="sr-only"
            :accept="ACCEPT"
            @change="onFileChange"
        />

        <div
            v-if="!model"
            role="button"
            tabindex="0"
            class="flex cursor-pointer flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed px-6 py-10 text-center transition-colors"
            :class="
                isDragging
                    ? 'border-sky-400 bg-sky-50 dark:bg-sky-950/30'
                    : 'border-border bg-muted/30 hover:border-sky-300 hover:bg-muted/50'
            "
            @click="openFilePicker"
            @keydown.enter.prevent="openFilePicker"
            @keydown.space.prevent="openFilePicker"
            @dragenter="onDragEnter"
            @dragover="onDragOver"
            @dragleave="onDragLeave"
            @drop="onDrop"
        >
            <div
                class="flex size-12 items-center justify-center rounded-full bg-sky-100 text-sky-600 dark:bg-sky-900/50 dark:text-sky-300"
            >
                <Upload class="size-5" />
            </div>
            <div>
                <p class="text-sm font-medium">
                    Drag and drop your invoice here
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    or click to browse — PDF, JPG, PNG, WEBP up to 8 MB
                </p>
            </div>
        </div>

        <div
            v-else
            class="flex items-center justify-between gap-3 rounded-xl border border-border bg-muted/30 px-4 py-3"
        >
            <div class="flex min-w-0 items-center gap-3">
                <div
                    class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-600 dark:bg-sky-900/50 dark:text-sky-300"
                >
                    <FileText class="size-5" />
                </div>
                <div class="min-w-0 text-left">
                    <p class="truncate text-sm font-medium">{{ model.name }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ formatFileSize(model.size) }}
                    </p>
                </div>
            </div>
            <Button
                type="button"
                variant="ghost"
                size="icon"
                class="shrink-0 text-muted-foreground hover:text-destructive"
                aria-label="Remove file"
                @click="clearFile"
            >
                <X class="size-4" />
            </Button>
        </div>

        <p v-if="localError || error" class="text-sm text-destructive">
            {{ localError ?? error }}
        </p>
    </div>
</template>
