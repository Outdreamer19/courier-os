<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PlatformLayout from '@/layouts/PlatformLayout.vue';

type TenantOption = { id: number; name: string; subdomain: string };

type ThreadRow = {
    id: number;
    subject: string;
    status: string;
    messages_count: number;
    last_message_at: string | null;
    last_message_human: string | null;
    preview: string | null;
    unread: boolean;
    tenant: { id: number; name: string; subdomain: string } | null;
};

type Paginated = {
    data: ThreadRow[];
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    threads: Paginated;
    tenants: TenantOption[];
    prefillTenantId?: number | null;
}>();

const composing = ref(Boolean(props.prefillTenantId));

const form = useForm({
    tenant_id: props.prefillTenantId ?? (props.tenants[0]?.id ?? null),
    subject: '',
    body: '',
});

const submit = () => {
    form.post('/platform/support', {
        onSuccess: () => {
            composing.value = false;
            form.reset('subject', 'body');
        },
    });
};
</script>

<template>
    <Head title="Support" />

    <PlatformLayout current="support">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl/8 font-semibold text-zinc-950 sm:text-xl/8">
                    Support
                </h1>
                <p class="mt-1 text-sm/6 text-zinc-500">
                    Conversations from courier businesses on the platform.
                </p>
            </div>
            <button
                type="button"
                class="rounded-lg bg-zinc-950 px-3 py-2 text-sm font-medium text-white hover:bg-zinc-800"
                @click="composing = !composing"
            >
                {{ composing ? 'Cancel' : 'Message a tenant' }}
            </button>
        </div>

        <form
            v-if="composing"
            class="mt-6 space-y-4 rounded-lg ring-1 ring-zinc-950/5 p-5"
            @submit.prevent="submit"
        >
            <div>
                <label class="text-sm font-medium text-zinc-950">Tenant</label>
                <select
                    v-model="form.tenant_id"
                    class="mt-1 block w-full rounded-lg border border-zinc-950/10 bg-white px-3 py-2 text-sm"
                    required
                >
                    <option
                        v-for="tenant in tenants"
                        :key="tenant.id"
                        :value="tenant.id"
                    >
                        {{ tenant.name }} ({{ tenant.subdomain }})
                    </option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-zinc-950">Subject</label>
                <input
                    v-model="form.subject"
                    type="text"
                    class="mt-1 block w-full rounded-lg border border-zinc-950/10 px-3 py-2 text-sm"
                    required
                    minlength="3"
                    maxlength="160"
                />
            </div>
            <div>
                <label class="text-sm font-medium text-zinc-950">Message</label>
                <textarea
                    v-model="form.body"
                    rows="4"
                    class="mt-1 block w-full rounded-lg border border-zinc-950/10 px-3 py-2 text-sm"
                    required
                />
            </div>
            <button
                type="submit"
                class="rounded-lg bg-zinc-950 px-3 py-2 text-sm font-medium text-white disabled:opacity-50"
                :disabled="form.processing"
            >
                Send
            </button>
        </form>

        <div class="mt-8 overflow-x-auto">
            <table class="min-w-full text-left text-sm/6 text-zinc-950">
                <thead class="text-zinc-500">
                    <tr class="border-b border-zinc-950/10">
                        <th class="py-3 pr-3 pl-0 font-medium">Tenant</th>
                        <th class="px-3 py-3 font-medium">Subject</th>
                        <th class="px-3 py-3 font-medium">Status</th>
                        <th class="py-3 pr-0 pl-3 font-medium">Last activity</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="thread in threads.data"
                        :key="thread.id"
                        class="border-b border-zinc-950/5 last:border-0"
                    >
                        <td class="py-4 pr-3 pl-0">
                            <Link
                                :href="`/platform/support/${thread.id}`"
                                class="font-medium hover:underline"
                            >
                                <span
                                    v-if="thread.unread"
                                    class="mr-1.5 inline-block size-1.5 rounded-full bg-blue-500 align-middle"
                                />
                                {{ thread.tenant?.name ?? '—' }}
                            </Link>
                        </td>
                        <td class="px-3 py-4">
                            <Link
                                :href="`/platform/support/${thread.id}`"
                                class="hover:underline"
                            >
                                {{ thread.subject }}
                            </Link>
                            <p
                                v-if="thread.preview"
                                class="mt-0.5 text-xs text-zinc-500"
                            >
                                {{ thread.preview }}
                            </p>
                        </td>
                        <td class="px-3 py-4 capitalize">
                            <span
                                class="inline-flex rounded-md px-1.5 py-0.5 text-xs font-medium"
                                :class="
                                    thread.status === 'open'
                                        ? 'bg-lime-400/20 text-lime-700'
                                        : 'bg-zinc-400/15 text-zinc-600'
                                "
                            >
                                {{ thread.status }}
                            </span>
                        </td>
                        <td class="py-4 pr-0 pl-3 text-zinc-500">
                            {{ thread.last_message_human ?? '—' }}
                        </td>
                    </tr>
                    <tr v-if="threads.data.length === 0">
                        <td
                            colspan="4"
                            class="py-12 text-center text-sm text-zinc-500"
                        >
                            No support conversations yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="threads.links.length > 3"
            class="mt-6 flex flex-wrap gap-2"
        >
            <button
                v-for="(link, i) in threads.links"
                :key="i"
                type="button"
                class="rounded-md px-2.5 py-1 text-sm"
                :class="
                    link.active
                        ? 'bg-zinc-950 text-white'
                        : 'text-zinc-600 hover:bg-zinc-950/5'
                "
                :disabled="!link.url"
                v-html="link.label"
                @click="link.url && router.get(link.url)"
            />
        </div>
    </PlatformLayout>
</template>
