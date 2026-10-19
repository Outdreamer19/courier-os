<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ChevronDown,
    Flame,
    Mail,
    MessageCircle,
    Phone,
    Snowflake,
    Thermometer,
} from 'lucide-vue-next';
import { computed, onMounted, reactive, ref } from 'vue';
import PlatformLayout from '@/layouts/PlatformLayout.vue';

type Lead = {
    id: number;
    status: string;
    priority: 'hot' | 'warm' | 'cold';
    score: number;
    recommended_plan: string | null;
    talking_points: string[];
    business_name: string | null;
    name: string;
    email: string;
    phone: string | null;
    preferred_channel: string;
    challenge: string | null;
    admin_notes: string | null;
    answers: Record<string, string | null>;
    needs: string[];
    source: string | null;
    created_at: string;
    created_human: string;
    contacted_at: string | null;
};

const props = defineProps<{
    leads: Lead[];
    statuses: string[];
}>();

const statusFilter = ref<string>('open');
const priorityFilter = ref<string>('all');
const search = ref('');
const openId = ref<number | null>(null);

const drafts = reactive<
    Record<number, { status: string; admin_notes: string }>
>({});
const saving = ref<number | null>(null);

onMounted(() => {
    const id = Number(new URLSearchParams(window.location.search).get('lead'));

    if (id && props.leads.some((l) => l.id === id)) {
        statusFilter.value = 'all';
        toggle(id);
    }
});

const priorityRank = { hot: 0, warm: 1, cold: 2 } as const;

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();

    return props.leads
        .filter((l) =>
            statusFilter.value === 'all'
                ? true
                : statusFilter.value === 'open'
                  ? !['won', 'lost'].includes(l.status)
                  : l.status === statusFilter.value,
        )
        .filter(
            (l) =>
                priorityFilter.value === 'all' ||
                l.priority === priorityFilter.value,
        )
        .filter(
            (l) =>
                !q ||
                [l.name, l.email, l.business_name, l.phone]
                    .filter(Boolean)
                    .some((v) => String(v).toLowerCase().includes(q)),
        )
        .sort((a, b) => {
            // New leads first, then hottest, then newest.
            const aNew = a.status === 'new' ? 0 : 1;
            const bNew = b.status === 'new' ? 0 : 1;

            return (
                aNew - bNew ||
                priorityRank[a.priority] - priorityRank[b.priority] ||
                b.created_at.localeCompare(a.created_at)
            );
        });
});

const counts = computed(() => ({
    new: props.leads.filter((l) => l.status === 'new').length,
    hot: props.leads.filter(
        (l) => l.priority === 'hot' && !['won', 'lost'].includes(l.status),
    ).length,
}));

function toggle(id: number) {
    openId.value = openId.value === id ? null : id;

    const lead = props.leads.find((l) => l.id === id);

    if (lead && !drafts[id]) {
        drafts[id] = {
            status: lead.status,
            admin_notes: lead.admin_notes ?? '',
        };
    }
}

function save(lead: Lead) {
    saving.value = lead.id;
    router.patch(`/platform/leads/${lead.id}`, drafts[lead.id], {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => (saving.value = null),
    });
}

function whatsapp(lead: Lead): string | null {
    if (!lead.phone) {
        return null;
    }

    const first = lead.name.split(/\s+/)[0];
    const text = `Hi ${first}, it's Shane from CourierOS. Thanks for sending your brief, I've had a look and have a few ideas for you.`;

    return `https://wa.me/${lead.phone.replace(/\D/g, '')}?text=${encodeURIComponent(text)}`;
}

const priorityStyle = {
    hot: 'bg-red-50 text-red-700 ring-red-600/20',
    warm: 'bg-amber-50 text-amber-800 ring-amber-600/20',
    cold: 'bg-sky-50 text-sky-700 ring-sky-600/20',
};
const priorityIcon = { hot: Flame, warm: Thermometer, cold: Snowflake };

const statusStyle: Record<string, string> = {
    new: 'bg-zinc-950 text-white',
    contacted: 'bg-blue-50 text-blue-700',
    qualified: 'bg-violet-50 text-violet-700',
    won: 'bg-emerald-50 text-emerald-700',
    lost: 'bg-zinc-100 text-zinc-500',
};

const planLabel: Record<string, string> = {
    starter: 'Starter',
    enterprise: 'Enterprise',
    custom: 'Custom build',
};

const selectClass =
    'rounded-lg border border-zinc-950/10 bg-white px-3 py-2 text-sm text-zinc-950 shadow-xs focus:border-zinc-950/20 focus:ring-2 focus:ring-zinc-950/10 focus:outline-none';
</script>

<template>
    <Head title="Leads" />

    <PlatformLayout current="leads">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl/8 font-semibold text-zinc-950 sm:text-xl/8">
                    Leads
                </h1>
                <p class="mt-1 text-sm/6 text-zinc-500">
                    Briefs from the courieros.co contact page.
                    <span v-if="counts.new" class="font-medium text-zinc-950"
                        >{{ counts.new }} new</span
                    ><span v-if="counts.new && counts.hot">, </span>
                    <span v-if="counts.hot" class="font-medium text-red-700"
                        >{{ counts.hot }} hot</span
                    >
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <input
                    v-model="search"
                    type="search"
                    placeholder="Search name, email, business…"
                    :class="[
                        selectClass,
                        'w-full max-w-xs placeholder:text-zinc-400',
                    ]"
                />
                <select v-model="statusFilter" :class="selectClass">
                    <option value="open">Open</option>
                    <option value="all">All statuses</option>
                    <option v-for="s in statuses" :key="s" :value="s">
                        {{ s.charAt(0).toUpperCase() + s.slice(1) }}
                    </option>
                </select>
                <select v-model="priorityFilter" :class="selectClass">
                    <option value="all">Any priority</option>
                    <option value="hot">Hot</option>
                    <option value="warm">Warm</option>
                    <option value="cold">Cold</option>
                </select>
            </div>
        </div>

        <div
            v-if="!filtered.length"
            class="mt-10 rounded-xl border border-dashed border-zinc-950/10 bg-white px-6 py-14 text-center text-sm text-zinc-500"
        >
            {{
                leads.length
                    ? 'No leads match these filters.'
                    : 'No leads yet. They will show up here as soon as someone sends a brief.'
            }}
        </div>

        <ul v-else class="mt-8 space-y-3">
            <li
                v-for="lead in filtered"
                :key="lead.id"
                class="overflow-hidden rounded-xl bg-white shadow-xs ring-1 ring-zinc-950/5"
            >
                <button
                    type="button"
                    class="flex w-full flex-wrap items-center gap-x-4 gap-y-2 px-4 py-3.5 text-left hover:bg-zinc-50 sm:px-5"
                    :aria-expanded="openId === lead.id"
                    @click="toggle(lead.id)"
                >
                    <span
                        class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-semibold capitalize ring-1 ring-inset"
                        :class="priorityStyle[lead.priority]"
                    >
                        <component
                            :is="priorityIcon[lead.priority]"
                            class="size-3.5"
                        />
                        {{ lead.priority }} · {{ lead.score }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span
                            class="block truncate text-sm font-semibold text-zinc-950"
                        >
                            {{ lead.business_name || lead.name }}
                        </span>
                        <span class="block truncate text-xs text-zinc-500">
                            <template v-if="lead.business_name"
                                >{{ lead.name }} ·
                            </template>
                            {{ lead.answers['Country'] }} ·
                            {{ lead.answers['Monthly volume'] }} ·
                            {{ lead.answers['Timeline'] }}
                        </span>
                    </span>
                    <span
                        v-if="lead.recommended_plan"
                        class="hidden rounded-md bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-700 sm:inline"
                        >{{
                            planLabel[lead.recommended_plan] ??
                            lead.recommended_plan
                        }}</span
                    >
                    <span
                        class="rounded-md px-2 py-1 text-xs font-medium capitalize"
                        :class="statusStyle[lead.status] ?? 'bg-zinc-100'"
                        >{{ lead.status }}</span
                    >
                    <span class="w-20 text-right text-xs text-zinc-500">{{
                        lead.created_human
                    }}</span>
                    <ChevronDown
                        class="size-4 text-zinc-400 transition"
                        :class="openId === lead.id && 'rotate-180'"
                    />
                </button>

                <div
                    v-if="openId === lead.id && drafts[lead.id]"
                    class="grid gap-6 border-t border-zinc-950/5 px-4 py-5 sm:px-5 lg:grid-cols-3"
                >
                    <!-- Answers -->
                    <div class="space-y-5 lg:col-span-2">
                        <dl class="grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                            <div
                                v-for="(value, label) in lead.answers"
                                :key="label"
                            >
                                <dt class="text-xs text-zinc-500">
                                    {{ label }}
                                </dt>
                                <dd class="font-medium text-zinc-950">
                                    {{ value }}
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs text-zinc-500">Needs</dt>
                                <dd class="mt-1 flex flex-wrap gap-1.5">
                                    <span
                                        v-for="n in lead.needs"
                                        :key="n"
                                        class="rounded-md bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-700"
                                        >{{ n }}</span
                                    >
                                </dd>
                            </div>
                            <div v-if="lead.challenge" class="sm:col-span-2">
                                <dt class="text-xs text-zinc-500">
                                    Biggest headache
                                </dt>
                                <dd
                                    class="mt-1 rounded-lg bg-zinc-50 p-3 text-zinc-800"
                                >
                                    “{{ lead.challenge }}”
                                </dd>
                            </div>
                            <div v-if="lead.source">
                                <dt class="text-xs text-zinc-500">Source</dt>
                                <dd class="font-medium text-zinc-950">
                                    {{ lead.source }}
                                </dd>
                            </div>
                        </dl>

                        <div
                            v-if="lead.talking_points.length"
                            class="rounded-lg bg-amber-50/60 p-4 ring-1 ring-amber-600/10"
                        >
                            <p
                                class="text-xs font-semibold tracking-wide text-amber-900 uppercase"
                            >
                                Before you reach out
                            </p>
                            <ul class="mt-2 space-y-1.5 text-sm text-zinc-800">
                                <li
                                    v-for="(p, i) in lead.talking_points"
                                    :key="i"
                                    class="flex gap-2"
                                >
                                    <span
                                        class="mt-2 size-1.5 shrink-0 rounded-full bg-amber-500"
                                    />
                                    {{ p }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <a
                                v-if="whatsapp(lead)"
                                :href="whatsapp(lead)!"
                                target="_blank"
                                rel="noopener"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium ring-1 ring-zinc-950/10 hover:bg-zinc-50"
                                :class="
                                    lead.preferred_channel === 'whatsapp' &&
                                    'bg-emerald-50 ring-emerald-600/20'
                                "
                            >
                                <MessageCircle
                                    class="size-4 text-emerald-600"
                                />
                                WhatsApp {{ lead.phone }}
                            </a>
                            <a
                                v-if="lead.phone"
                                :href="`tel:${lead.phone}`"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium ring-1 ring-zinc-950/10 hover:bg-zinc-50"
                                :class="
                                    lead.preferred_channel === 'call' &&
                                    'bg-emerald-50 ring-emerald-600/20'
                                "
                            >
                                <Phone class="size-4 text-zinc-500" />
                                Call
                            </a>
                            <a
                                :href="`mailto:${lead.email}?subject=${encodeURIComponent('Your CourierOS brief')}`"
                                class="flex items-center gap-2 truncate rounded-lg px-3 py-2 text-sm font-medium ring-1 ring-zinc-950/10 hover:bg-zinc-50"
                                :class="
                                    lead.preferred_channel === 'email' &&
                                    'bg-emerald-50 ring-emerald-600/20'
                                "
                            >
                                <Mail class="size-4 text-zinc-500" />
                                {{ lead.email }}
                            </a>
                        </div>

                        <label class="block text-sm">
                            <span class="text-xs text-zinc-500">Status</span>
                            <select
                                v-model="drafts[lead.id].status"
                                :class="[selectClass, 'mt-1 w-full capitalize']"
                            >
                                <option
                                    v-for="s in statuses"
                                    :key="s"
                                    :value="s"
                                >
                                    {{ s }}
                                </option>
                            </select>
                        </label>
                        <label class="block text-sm">
                            <span class="text-xs text-zinc-500">Notes</span>
                            <textarea
                                v-model="drafts[lead.id].admin_notes"
                                rows="4"
                                :class="[
                                    selectClass,
                                    'mt-1 w-full resize-none',
                                ]"
                                placeholder="What was agreed, next follow up…"
                            />
                        </label>
                        <button
                            type="button"
                            class="w-full rounded-lg bg-zinc-950 px-3 py-2 text-sm font-semibold text-white hover:bg-zinc-800 disabled:opacity-60"
                            :disabled="saving === lead.id"
                            @click="save(lead)"
                        >
                            {{ saving === lead.id ? 'Saving…' : 'Save' }}
                        </button>
                    </div>
                </div>
            </li>
        </ul>
    </PlatformLayout>
</template>
