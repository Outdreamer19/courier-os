<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import PlatformLayout from '@/layouts/PlatformLayout.vue';

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
    tenant: {
        id: number;
        name: string;
        subdomain: string;
        status: string;
    } | null;
    messages: Message[];
};

const props = defineProps<{ thread: Thread }>();

const replyForm = useForm({ body: '' });
const statusForm = useForm({
    status: props.thread.status === 'open' ? 'closed' : 'open',
});

const sendReply = () => {
    replyForm.post(`/platform/support/${props.thread.id}/messages`, {
        preserveScroll: true,
        onSuccess: () => replyForm.reset('body'),
    });
};

const toggleStatus = () => {
    statusForm.status = props.thread.status === 'open' ? 'closed' : 'open';
    statusForm.patch(`/platform/support/${props.thread.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="thread.subject" />

    <PlatformLayout current="support">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <Link
                    href="/platform/support"
                    class="text-sm text-zinc-500 hover:text-zinc-950"
                >
                    ← Support
                </Link>
                <h1
                    class="mt-2 text-2xl/8 font-semibold text-zinc-950 sm:text-xl/8"
                >
                    {{ thread.subject }}
                </h1>
                <p class="mt-1 text-sm/6 text-zinc-500">
                    {{ thread.tenant?.name }} · {{ thread.tenant?.subdomain }}
                    <span
                        class="ml-2 inline-flex rounded-md px-1.5 py-0.5 text-xs font-medium capitalize"
                        :class="
                            thread.status === 'open'
                                ? 'bg-lime-400/20 text-lime-700'
                                : 'bg-zinc-400/15 text-zinc-600'
                        "
                    >
                        {{ thread.status }}
                    </span>
                </p>
            </div>
            <button
                type="button"
                class="rounded-lg border border-zinc-950/10 px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-950/5"
                @click="toggleStatus"
            >
                {{ thread.status === 'open' ? 'Close' : 'Reopen' }}
            </button>
        </div>

        <div class="mt-8 space-y-4">
            <div
                v-for="message in thread.messages"
                :key="message.id"
                class="rounded-lg p-4 ring-1 ring-zinc-950/5"
                :class="
                    message.author_side === 'platform'
                        ? 'bg-zinc-950/[0.02]'
                        : 'bg-white'
                "
            >
                <div class="flex items-baseline justify-between gap-3">
                    <p class="text-sm font-medium text-zinc-950">
                        {{ message.user?.name ?? 'Unknown' }}
                        <span class="ml-2 text-xs font-normal text-zinc-500">
                            {{
                                message.author_side === 'platform'
                                    ? 'CourierOS'
                                    : 'Tenant'
                            }}
                        </span>
                    </p>
                    <p class="text-xs text-zinc-500">
                        {{ message.created_at_human }}
                    </p>
                </div>
                <p class="mt-2 whitespace-pre-wrap text-sm/6 text-zinc-700">
                    {{ message.body }}
                </p>
            </div>
        </div>

        <form class="mt-8 space-y-3" @submit.prevent="sendReply">
            <label class="text-sm font-medium text-zinc-950">Reply</label>
            <textarea
                v-model="replyForm.body"
                rows="4"
                class="block w-full rounded-lg border border-zinc-950/10 px-3 py-2 text-sm"
                required
                placeholder="Write a reply…"
            />
            <button
                type="submit"
                class="rounded-lg bg-zinc-950 px-3 py-2 text-sm font-medium text-white disabled:opacity-50"
                :disabled="replyForm.processing"
            >
                Send reply
            </button>
        </form>
    </PlatformLayout>
</template>
