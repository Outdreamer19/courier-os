<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Message = {
    id: number;
    body: string;
    author_side: 'tenant' | 'platform';
    created_at: string | null;
    created_at_human: string | null;
    user: { id: number; name: string; email: string } | null;
};

type Thread = {
    id: number;
    subject: string;
    status: string;
    last_message_at: string | null;
    closed_at: string | null;
    messages: Message[];
};

const props = defineProps<{ thread: Thread }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Platform support', href: '/admin/platform-support' },
    { title: props.thread.subject, href: `/admin/platform-support/${props.thread.id}` },
];

const replyForm = useForm({ body: '' });
const statusForm = useForm({
    status: props.thread.status === 'open' ? 'closed' : 'open',
});

const sendReply = () => {
    replyForm.post(`/admin/platform-support/${props.thread.id}/messages`, {
        preserveScroll: true,
        onSuccess: () => replyForm.reset('body'),
    });
};

const toggleStatus = () => {
    statusForm.status = props.thread.status === 'open' ? 'closed' : 'open';
    statusForm.patch(`/admin/platform-support/${props.thread.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="thread.subject" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <Link
                        href="/admin/platform-support"
                        class="text-sm text-muted-foreground hover:text-foreground"
                    >
                        ← Platform support
                    </Link>
                    <h1 class="mt-2 text-xl font-semibold tracking-tight">
                        {{ thread.subject }}
                    </h1>
                    <p class="mt-1 text-sm capitalize text-muted-foreground">
                        {{ thread.status }}
                    </p>
                </div>
                <button
                    type="button"
                    class="rounded-lg border px-3 py-2 text-sm font-medium"
                    @click="toggleStatus"
                >
                    {{ thread.status === 'open' ? 'Close' : 'Reopen' }}
                </button>
            </div>

            <div class="space-y-4">
                <div
                    v-for="message in thread.messages"
                    :key="message.id"
                    class="rounded-xl border p-4"
                    :class="
                        message.author_side === 'platform'
                            ? 'bg-muted/40'
                            : 'bg-background'
                    "
                >
                    <div class="flex items-baseline justify-between gap-3">
                        <p class="text-sm font-medium">
                            {{ message.user?.name ?? 'Unknown' }}
                            <span
                                class="ml-2 text-xs font-normal text-muted-foreground"
                            >
                                {{
                                    message.author_side === 'platform'
                                        ? 'CourierOS'
                                        : 'Your team'
                                }}
                            </span>
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ message.created_at_human }}
                        </p>
                    </div>
                    <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed">
                        {{ message.body }}
                    </p>
                </div>
            </div>

            <form class="space-y-3" @submit.prevent="sendReply">
                <label class="text-sm font-medium">Reply</label>
                <textarea
                    v-model="replyForm.body"
                    rows="4"
                    class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                    required
                    placeholder="Write a reply…"
                />
                <button
                    type="submit"
                    class="rounded-lg bg-primary px-3 py-2 text-sm font-medium text-primary-foreground disabled:opacity-50"
                    :disabled="replyForm.processing"
                >
                    Send reply
                </button>
            </form>
        </div>
    </AppLayout>
</template>
