<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type ThreadRow = {
    id: number;
    subject: string;
    status: string;
    messages_count: number;
    last_message_at: string | null;
    last_message_human: string | null;
    preview: string | null;
    unread: boolean;
};

type Paginated = {
    data: ThreadRow[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{ threads: Paginated }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Platform support', href: '/admin/platform-support' },
];

const composing = ref(false);
const form = useForm({ subject: '', body: '' });

const submit = () => {
    form.post('/admin/platform-support', {
        onSuccess: () => {
            composing.value = false;
            form.reset();
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Platform support" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-xl font-semibold tracking-tight">
                        Platform support
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Ask CourierOS about billing, bugs, or product changes.
                    </p>
                </div>
                <button
                    type="button"
                    class="rounded-lg bg-primary px-3 py-2 text-sm font-medium text-primary-foreground"
                    @click="composing = !composing"
                >
                    {{ composing ? 'Cancel' : 'New message' }}
                </button>
            </div>

            <form
                v-if="composing"
                class="space-y-4 rounded-xl border p-5"
                @submit.prevent="submit"
            >
                <div>
                    <label class="text-sm font-medium">Subject</label>
                    <input
                        v-model="form.subject"
                        type="text"
                        class="mt-1 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        required
                        minlength="3"
                        maxlength="160"
                    />
                </div>
                <div>
                    <label class="text-sm font-medium">Message</label>
                    <textarea
                        v-model="form.body"
                        rows="4"
                        class="mt-1 flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        required
                    />
                </div>
                <button
                    type="submit"
                    class="rounded-lg bg-primary px-3 py-2 text-sm font-medium text-primary-foreground disabled:opacity-50"
                    :disabled="form.processing"
                >
                    Send to CourierOS
                </button>
            </form>

            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-muted/40 text-left text-muted-foreground">
                            <th class="px-4 py-3 font-medium">Subject</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Last activity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="thread in threads.data"
                            :key="thread.id"
                            class="border-b last:border-0"
                        >
                            <td class="px-4 py-3">
                                <Link
                                    :href="`/admin/platform-support/${thread.id}`"
                                    class="font-medium hover:underline"
                                >
                                    <span
                                        v-if="thread.unread"
                                        class="mr-1.5 inline-block size-1.5 rounded-full bg-blue-500 align-middle"
                                    />
                                    {{ thread.subject }}
                                </Link>
                                <p
                                    v-if="thread.preview"
                                    class="mt-0.5 text-xs text-muted-foreground"
                                >
                                    {{ thread.preview }}
                                </p>
                            </td>
                            <td class="px-4 py-3 capitalize">
                                {{ thread.status }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ thread.last_message_human ?? '—' }}
                            </td>
                        </tr>
                        <tr v-if="threads.data.length === 0">
                            <td
                                colspan="3"
                                class="px-4 py-12 text-center text-muted-foreground"
                            >
                                No conversations yet. Start one when you need
                                help from CourierOS.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="threads.links.length > 3"
                class="flex flex-wrap gap-2"
            >
                <button
                    v-for="(link, i) in threads.links"
                    :key="i"
                    type="button"
                    class="rounded-md px-2.5 py-1 text-sm"
                    :class="
                        link.active
                            ? 'bg-primary text-primary-foreground'
                            : 'text-muted-foreground hover:bg-muted'
                    "
                    :disabled="!link.url"
                    v-html="link.label"
                    @click="link.url && router.get(link.url)"
                />
            </div>
        </div>
    </AppLayout>
</template>
