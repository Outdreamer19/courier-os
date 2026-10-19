<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    ArrowRightLeft,
    Building2,
    ChartColumn,
    Check,
    ChevronDown,
    CircleHelp,
    CirclePlay,
    Clock,
    CreditCard,
    Globe,
    Hammer,
    Link2,
    LoaderCircle,
    Mail,
    MapPin,
    MessageCircle,
    Moon,
    PackageSearch,
    Phone,
    Rocket,
    Send,
    Smartphone,
    Sparkles,
    Sun,
    Sunset,
    TrendingUp,
    Users,
    Warehouse,
} from 'lucide-vue-next';
import type { Component } from 'vue';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import MarketingFooter from '@/components/marketing/MarketingFooter.vue';
import MarketingNav from '@/components/marketing/MarketingNav.vue';
import {
    currentCampaign,
    trackMarketing,
    whatsappLink,
} from '@/composables/useMarketingTracking';

type Option = { value: string; label: string; hint: string | null };

const props = defineProps<{
    options: {
        stages: Option[];
        countries: Option[];
        volumes: Option[];
        forwarders: Option[];
        needs: Option[];
        timelines: Option[];
        plans: Option[];
        channels: Option[];
        times: Option[];
    };
    sales: { whatsapp: string | null };
    liveDemo: { url: string; name: string } | null;
}>();

/* ------------------------------------------------------------------ */
/* Icons per option key. Anything unmapped simply renders without one. */
/* ------------------------------------------------------------------ */
const icons: Record<string, Component> = {
    // stages
    starting: Rocket,
    manual: MessageCircle,
    switching: ArrowRightLeft,
    scaling: TrendingUp,
    // forwarders
    own: Warehouse,
    partner: Users,
    need: MapPin,
    unsure: CircleHelp,
    // needs
    website: Globe,
    portal: Smartphone,
    prealerts: PackageSearch,
    tracking: MessageCircle,
    payments: CreditCard,
    migration: ArrowRightLeft,
    custom_domain: Link2,
    multi_location: Building2,
    reporting: ChartColumn,
    // plans
    starter: Rocket,
    done_for_you: Sparkles,
    custom: Hammer,
    // channels
    whatsapp: MessageCircle,
    call: Phone,
    email: Mail,
    // times
    morning: Sun,
    afternoon: Sunset,
    evening: Moon,
    anytime: Clock,
};

/* ------------------------------------------------------------------ */
/* Form state                                                          */
/* ------------------------------------------------------------------ */
const form = useForm({
    stage: '',
    business_name: '',
    country: '',
    monthly_volume: '',
    forwarder: '',
    needs: [] as string[],
    timeline: '',
    plan_interest: '',
    challenge: '',
    name: '',
    email: '',
    phone: '',
    preferred_channel: '',
    best_time: '',
    utm_source: '',
    utm_campaign: '',
    referrer: '',
    website: '',
});

const DRAFT_KEY = 'courieros.contact.draft.v1';
const draftFields = [
    'stage',
    'business_name',
    'country',
    'monthly_volume',
    'forwarder',
    'needs',
    'timeline',
    'plan_interest',
    'challenge',
    'name',
    'email',
    'phone',
    'preferred_channel',
    'best_time',
] as const;

const steps = [
    {
        key: 'business',
        title: 'Your business',
        blurb: 'Where things stand today',
        fields: [
            'stage',
            'business_name',
            'country',
            'monthly_volume',
            'forwarder',
        ],
    },
    {
        key: 'needs',
        title: 'What you need',
        blurb: 'So we come prepared',
        fields: ['needs', 'timeline', 'plan_interest', 'challenge'],
    },
    {
        key: 'contact',
        title: 'How to reach you',
        blurb: 'We reply within one working day',
        fields: ['name', 'email', 'phone', 'preferred_channel', 'best_time'],
    },
] as const;

const step = ref(0);
const direction = ref<'forward' | 'back'>('forward');
const submitted = ref(false);
const submittedName = ref('');
const submittedChannel = ref('');
const submittedTime = ref('');

/* ------------------------------------------------------------------ */
/* Validation                                                          */
/* ------------------------------------------------------------------ */
const emailOk = computed(() =>
    /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(form.email.trim()),
);
const needsPhone = computed(
    () =>
        form.preferred_channel === 'whatsapp' ||
        form.preferred_channel === 'call',
);
const phoneOk = computed(
    () => !needsPhone.value || form.phone.replace(/\D/g, '').length >= 7,
);

const stepValid = computed(() => [
    Boolean(
        form.stage && form.country && form.monthly_volume && form.forwarder,
    ),
    Boolean(form.needs.length && form.timeline && form.plan_interest),
    Boolean(
        form.name.trim() &&
        emailOk.value &&
        form.preferred_channel &&
        phoneOk.value &&
        form.best_time,
    ),
]);

const requiredAnswers = computed(() => [
    form.stage,
    form.country,
    form.monthly_volume,
    form.forwarder,
    form.needs.length ? 'y' : '',
    form.timeline,
    form.plan_interest,
    form.name.trim(),
    emailOk.value ? 'y' : '',
    form.preferred_channel,
    phoneOk.value && form.preferred_channel ? 'y' : '',
    form.best_time,
]);

const progress = computed(() => {
    const done = requiredAnswers.value.filter(Boolean).length;

    return Math.round((done / requiredAnswers.value.length) * 100);
});

const touched = ref<Record<string, boolean>>({});

function fieldError(field: string): string | undefined {
    const server = (form.errors as Record<string, string | undefined>)[field];

    if (server) {
        return server;
    }

    if (!touched.value[field]) {
        return undefined;
    }

    if (field === 'email' && form.email && !emailOk.value) {
        return 'Check that email address.';
    }

    if (field === 'phone' && needsPhone.value && !phoneOk.value) {
        return 'Add a number so we can reach you.';
    }

    return undefined;
}

/* ------------------------------------------------------------------ */
/* Progressive reveal: each question appears once the one before it   */
/* is answered, so the form feels like a conversation.                */
/* ------------------------------------------------------------------ */
const show = computed(() => ({
    country: Boolean(form.stage),
    volume: Boolean(form.stage && form.country),
    forwarder: Boolean(form.stage && form.country && form.monthly_volume),
    timeline: form.needs.length > 0,
    plan: form.needs.length > 0 && Boolean(form.timeline),
    challenge:
        form.needs.length > 0 && Boolean(form.timeline && form.plan_interest),
}));

function revealNext(id: string) {
    nextTick(() => {
        const el = document.getElementById(id);

        if (!el) {
            return;
        }

        const rect = el.getBoundingClientRect();

        if (rect.bottom > window.innerHeight - 40) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
}

watch(
    () => form.stage,
    (v, old) => v && !old && revealNext('q-country'),
);
watch(
    () => form.country,
    (v, old) => v && !old && revealNext('q-volume'),
);
watch(
    () => form.monthly_volume,
    (v, old) => v && !old && revealNext('q-forwarder'),
);
watch(
    () => form.needs.length,
    (v, old) => v > 0 && old === 0 && revealNext('q-timeline'),
);
watch(
    () => form.timeline,
    (v, old) => v && !old && revealNext('q-plan'),
);
watch(
    () => form.plan_interest,
    (v, old) => v && !old && revealNext('q-challenge'),
);

function toggleNeed(value: string) {
    const i = form.needs.indexOf(value);

    if (i === -1) {
        form.needs.push(value);
    } else {
        form.needs.splice(i, 1);
    }

    form.clearErrors('needs');
}

type SingleChoice =
    | 'stage'
    | 'monthly_volume'
    | 'forwarder'
    | 'timeline'
    | 'plan_interest'
    | 'preferred_channel'
    | 'best_time';

function pick(field: SingleChoice, value: string) {
    form[field] = value;
    form.clearErrors(field);
}

/* ------------------------------------------------------------------ */
/* Navigation                                                          */
/* ------------------------------------------------------------------ */
const formTop = ref<HTMLElement | null>(null);

function scrollToForm() {
    nextTick(() => {
        const el = formTop.value;

        if (el && el.getBoundingClientRect().top < 0) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
}

function goTo(index: number) {
    if (index === step.value || index < 0 || index > 2) {
        return;
    }

    // Only allow jumping forward past steps that are complete.
    if (index > step.value && !stepValid.value.slice(0, index).every(Boolean)) {
        return;
    }

    direction.value = index > step.value ? 'forward' : 'back';
    step.value = index;
    scrollToForm();
}

function next() {
    if (!stepValid.value[step.value]) {
        steps[step.value].fields.forEach((f) => (touched.value[f] = true));

        return;
    }

    if (step.value === 0) {
        trackMarketing('Contact', { content_name: 'contact_step_1' });
    }

    goTo(step.value + 1);
}

function back() {
    goTo(step.value - 1);
}

function onEnter(e: KeyboardEvent) {
    const target = e.target as HTMLElement;

    if (target.tagName === 'TEXTAREA') {
        return;
    }

    e.preventDefault();

    if (step.value < 2) {
        next();
    } else {
        submit();
    }
}

/* ------------------------------------------------------------------ */
/* Submit                                                              */
/* ------------------------------------------------------------------ */
function stepForField(field: string): number {
    const root = field.split('.')[0];

    return steps.findIndex((s) =>
        (s.fields as readonly string[]).includes(root),
    );
}

function submit() {
    ['name', 'email', 'phone'].forEach((f) => (touched.value[f] = true));

    if (!stepValid.value.every(Boolean)) {
        const firstBad = stepValid.value.findIndex((ok) => !ok);

        if (firstBad !== step.value) {
            direction.value = 'back';
            step.value = firstBad;
        }

        return;
    }

    form.post('/contact/enquiry', {
        preserveScroll: true,
        // Keep this component mounted through the redirect back to
        // /contact, otherwise the success screen state is lost.
        preserveState: true,
        onSuccess: () => {
            submittedName.value = form.name.trim().split(/\s+/)[0] ?? '';
            submittedChannel.value = form.preferred_channel;
            submittedTime.value = form.best_time;
            submitted.value = true;
            clearDraft();
            trackMarketing('Lead', {
                content_name: 'contact_form',
                value: form.plan_interest,
            });
            nextTick(() =>
                formTop.value?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                }),
            );
        },
        onError: (errors) => {
            const steps = Object.keys(errors)
                .map(stepForField)
                .filter((i) => i >= 0);

            if (steps.length) {
                const first = Math.min(...steps);

                direction.value = first < step.value ? 'back' : 'forward';
                step.value = first;
            }
        },
    });
}

/* ------------------------------------------------------------------ */
/* Draft: keep answers if the visitor wanders off and comes back.      */
/* ------------------------------------------------------------------ */
function saveDraft() {
    try {
        const data: Record<string, unknown> = {};

        draftFields.forEach((f) => (data[f] = form[f]));
        window.localStorage.setItem(DRAFT_KEY, JSON.stringify(data));
    } catch {
        // storage unavailable: drafts are a nicety, not a requirement
    }
}

function clearDraft() {
    try {
        window.localStorage.removeItem(DRAFT_KEY);
    } catch {
        // ignore
    }
}

const restoredDraft = ref(false);

onMounted(() => {
    const params = new URLSearchParams(window.location.search);

    form.utm_source = params.get('utm_source') ?? '';
    form.utm_campaign = currentCampaign() ?? '';
    form.referrer = document.referrer.slice(0, 500);

    try {
        const raw = window.localStorage.getItem(DRAFT_KEY);

        if (raw) {
            const data = JSON.parse(raw) as Record<string, unknown>;

            draftFields.forEach((f) => {
                const v = data[f];

                if (f === 'needs' && Array.isArray(v)) {
                    form.needs = v.filter((x) => typeof x === 'string');
                } else if (f !== 'needs' && typeof v === 'string') {
                    (form as unknown as Record<string, string>)[f] = v;
                }
            });

            restoredDraft.value = Boolean(form.stage);
        }
    } catch {
        // ignore a corrupt or blocked draft
    }
});

watch(
    () => draftFields.map((f) => form[f]),
    () => !submitted.value && saveDraft(),
    { deep: true },
);

function startOver() {
    clearDraft();
    form.reset();
    form.clearErrors();
    touched.value = {};
    restoredDraft.value = false;
    direction.value = 'back';
    step.value = 0;
}

/* ------------------------------------------------------------------ */
/* Live brief in the sidebar                                           */
/* ------------------------------------------------------------------ */
function labelOf(group: Option[], value: string): string {
    return group.find((o) => o.value === value)?.label ?? '';
}

const brief = computed(() => [
    {
        label: 'Where you are',
        value: labelOf(props.options.stages, form.stage),
    },
    {
        label: 'Business',
        value: form.business_name.trim(),
        optional: true,
    },
    {
        label: 'Based in',
        value: labelOf(props.options.countries, form.country),
    },
    {
        label: 'Packages a month',
        value: labelOf(props.options.volumes, form.monthly_volume),
    },
    {
        label: 'US warehouse',
        value: labelOf(props.options.forwarders, form.forwarder),
    },
    {
        label: 'Timeline',
        value: labelOf(props.options.timelines, form.timeline),
    },
    {
        label: 'Looking for',
        value: labelOf(props.options.plans, form.plan_interest),
    },
]);

const briefNeeds = computed(() =>
    form.needs.map((n) => labelOf(props.options.needs, n)).filter(Boolean),
);

const stageCopy: Record<string, string> = {
    starting:
        'Starting fresh is the easiest time to set things up properly. We will walk you through launch week.',
    manual: 'Most couriers we speak to start here. Pre-alerts and automatic updates are usually the first big win.',
    switching:
        'We will ask what you use today so we can plan moving your customers across.',
    scaling:
        'We will talk about staff accounts, locations and how you handle volume today.',
};

const reassurance = computed(() => stageCopy[form.stage] ?? '');

/* ------------------------------------------------------------------ */
/* Success screen copy                                                 */
/* ------------------------------------------------------------------ */
const channelPhrase = computed(() => {
    const time = labelOf(
        props.options.times,
        submittedTime.value,
    ).toLowerCase();
    const when = submittedTime.value === 'anytime' ? '' : ` in the ${time}`;

    switch (submittedChannel.value) {
        case 'whatsapp':
            return `Expect a WhatsApp message from us${when}, within one working day.`;
        case 'call':
            return `We will give you a call${when}, within one working day.`;
        default:
            return 'We will reply by email within one working day.';
    }
});

const salesChat = computed(() =>
    props.sales.whatsapp
        ? whatsappLink(
              props.sales.whatsapp,
              submitted.value
                  ? 'Hi, I just sent my brief on the CourierOS contact page.'
                  : 'Hi, I found CourierOS and would like to talk about my courier business.',
          )
        : null,
);

const cardBase =
    'group relative flex w-full items-start gap-3 rounded-2xl border bg-white p-4 text-left transition duration-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-marketing-orange';
const cardIdle =
    'border-marketing-border hover:-translate-y-0.5 hover:border-marketing-ink/30 hover:shadow-[0_12px_30px_-18px_rgba(17,17,17,0.35)]';
const cardActive =
    'border-marketing-orange bg-marketing-orange/[0.04] shadow-[0_12px_30px_-18px_rgba(226,83,10,0.55)] ring-1 ring-marketing-orange';

const chipBase =
    'inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium transition duration-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-marketing-orange';
const chipIdle =
    'border-marketing-border bg-white text-marketing-ink hover:border-marketing-ink/30';
const chipActive = 'border-marketing-ink bg-marketing-ink text-marketing-cream';

const inputClass =
    'mt-2 w-full rounded-xl border border-marketing-border bg-white px-4 py-3 text-base text-marketing-ink shadow-xs transition placeholder:text-marketing-ink-muted/60 focus:border-marketing-orange focus:ring-2 focus:ring-marketing-orange/20 focus:outline-none';
</script>

<template>
    <Head title="Contact us">
        <meta
            name="description"
            content="Tell us about your courier business in three quick steps and we will come back with the right setup, pricing and next steps."
        />
        <meta property="og:type" content="website" />
        <meta property="og:title" content="Contact CourierOS" />
        <meta
            property="og:description"
            content="Tell us about your courier business in three quick steps and we will come back with the right setup, pricing and next steps."
        />
        <meta
            property="og:image"
            content="/images/marketing/og/courieros-og.png"
        />
        <meta name="twitter:card" content="summary_large_image" />
    </Head>

    <div
        class="min-h-screen bg-marketing-cream font-marketing text-marketing-ink"
    >
        <MarketingNav />

        <!-- Hero -->
        <section class="relative overflow-hidden pt-14 pb-8 sm:pt-20">
            <div
                aria-hidden="true"
                class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-96 bg-[radial-gradient(60%_60%_at_50%_0%,rgba(255,122,0,0.14),transparent_70%)]"
            />
            <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
                <p
                    class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase"
                >
                    Contact us
                </p>
                <h1
                    class="mt-3 text-4xl font-semibold tracking-tight text-balance sm:text-5xl"
                >
                    Tell us about your courier business
                </h1>
                <p
                    class="mx-auto mt-5 max-w-xl text-base text-pretty text-marketing-ink-muted"
                >
                    Three quick steps, about two minutes. Your answers mean our
                    first reply already has the right setup and price, not more
                    questions.
                </p>
            </div>
        </section>

        <section class="pb-24">
            <div
                ref="formTop"
                class="mx-auto grid max-w-6xl scroll-mt-24 gap-8 px-4 sm:px-6 lg:grid-cols-12 lg:px-8"
            >
                <!-- Form card -->
                <div class="lg:col-span-8">
                    <div
                        class="overflow-hidden rounded-3xl border border-marketing-border bg-white/70 shadow-[0_30px_80px_-40px_rgba(17,17,17,0.25)] backdrop-blur"
                    >
                        <!-- Success -->
                        <div
                            v-if="submitted"
                            class="success-in px-6 py-14 text-center sm:px-12"
                            role="status"
                            aria-live="polite"
                        >
                            <div
                                class="mx-auto flex size-16 items-center justify-center rounded-full bg-marketing-orange text-white shadow-[0_14px_40px_-12px_rgba(226,83,10,0.7)]"
                            >
                                <Check
                                    class="check-pop size-8"
                                    stroke-width="3"
                                />
                            </div>
                            <h2
                                class="mt-6 text-3xl font-semibold tracking-tight"
                            >
                                Thanks{{
                                    submittedName ? `, ${submittedName}` : ''
                                }}. Your brief is with us.
                            </h2>
                            <p
                                class="mx-auto mt-3 max-w-md text-marketing-ink-muted"
                            >
                                {{ channelPhrase }} We will already know where
                                you are and what you need, so we can get
                                straight to the useful part.
                            </p>

                            <div
                                class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row"
                            >
                                <a
                                    v-if="liveDemo"
                                    :href="liveDemo.url"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-2 rounded-xl bg-marketing-ink px-5 py-3 text-sm font-semibold text-marketing-cream transition hover:-translate-y-0.5"
                                >
                                    <CirclePlay class="size-4" />
                                    Explore the live demo while you wait
                                </a>
                                <a
                                    v-if="salesChat"
                                    :href="salesChat"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-2 rounded-xl border border-marketing-ink px-5 py-3 text-sm font-semibold transition hover:-translate-y-0.5 hover:bg-marketing-ink hover:text-marketing-cream"
                                    @click="
                                        trackMarketing('Contact', {
                                            content_name:
                                                'contact_success_whatsapp',
                                        })
                                    "
                                >
                                    <MessageCircle class="size-4" />
                                    Can't wait? WhatsApp us
                                </a>
                            </div>
                            <button
                                type="button"
                                class="mt-8 text-sm text-marketing-ink-muted underline-offset-4 hover:underline"
                                @click="
                                    submitted = false;
                                    startOver();
                                "
                            >
                                Send another enquiry
                            </button>
                        </div>

                        <form
                            v-else
                            novalidate
                            @submit.prevent="submit"
                            @keydown.enter="onEnter"
                        >
                            <!-- Stepper -->
                            <div
                                class="border-b border-marketing-border bg-white px-5 pt-5 pb-4 sm:px-8"
                            >
                                <ol class="grid grid-cols-3 gap-2">
                                    <li v-for="(s, i) in steps" :key="s.key">
                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-2.5 rounded-xl p-1.5 text-left transition disabled:cursor-not-allowed"
                                            :disabled="
                                                i > step &&
                                                !stepValid
                                                    .slice(0, i)
                                                    .every(Boolean)
                                            "
                                            :aria-current="
                                                i === step ? 'step' : undefined
                                            "
                                            @click="goTo(i)"
                                        >
                                            <span
                                                class="flex size-8 shrink-0 items-center justify-center rounded-full text-sm font-semibold transition duration-300"
                                                :class="
                                                    i < step && stepValid[i]
                                                        ? 'bg-marketing-orange text-white'
                                                        : i === step
                                                          ? 'bg-marketing-ink text-marketing-cream'
                                                          : 'bg-marketing-ink/5 text-marketing-ink-muted'
                                                "
                                            >
                                                <Check
                                                    v-if="
                                                        i < step && stepValid[i]
                                                    "
                                                    class="size-4"
                                                    stroke-width="3"
                                                />
                                                <template v-else>{{
                                                    i + 1
                                                }}</template>
                                            </span>
                                            <span class="min-w-0">
                                                <span
                                                    class="block truncate text-sm font-semibold"
                                                    :class="
                                                        i === step
                                                            ? 'text-marketing-ink'
                                                            : 'text-marketing-ink-muted'
                                                    "
                                                    >{{ s.title }}</span
                                                >
                                                <span
                                                    class="hidden truncate text-xs text-marketing-ink-muted sm:block"
                                                    >{{ s.blurb }}</span
                                                >
                                            </span>
                                        </button>
                                    </li>
                                </ol>
                                <div
                                    class="mt-4 h-1.5 overflow-hidden rounded-full bg-marketing-ink/5"
                                    role="progressbar"
                                    :aria-valuenow="progress"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                    aria-label="Form progress"
                                >
                                    <div
                                        class="progress-fill h-full rounded-full transition-[width] duration-500 ease-out"
                                        :style="{
                                            width: `${Math.max(progress, 4)}%`,
                                        }"
                                    />
                                </div>
                                <div
                                    v-if="restoredDraft && step === 0"
                                    class="mt-3 flex items-center justify-between gap-3 rounded-lg bg-marketing-amber/15 px-3 py-2 text-xs text-marketing-ink"
                                >
                                    <span
                                        >Welcome back. We kept your
                                        answers.</span
                                    >
                                    <button
                                        type="button"
                                        class="font-semibold underline-offset-4 hover:underline"
                                        @click="startOver"
                                    >
                                        Start over
                                    </button>
                                </div>
                            </div>

                            <!-- Honeypot -->
                            <div class="hidden" aria-hidden="true">
                                <label>
                                    Leave this empty
                                    <input
                                        v-model="form.website"
                                        type="text"
                                        tabindex="-1"
                                        autocomplete="off"
                                    />
                                </label>
                            </div>

                            <div class="relative px-5 py-8 sm:px-8">
                                <Transition
                                    :name="
                                        direction === 'forward'
                                            ? 'step-forward'
                                            : 'step-back'
                                    "
                                    mode="out-in"
                                >
                                    <!-- STEP 1 -->
                                    <div
                                        v-if="step === 0"
                                        key="s1"
                                        class="space-y-10"
                                    >
                                        <fieldset>
                                            <legend
                                                class="text-lg font-semibold"
                                            >
                                                Which sounds most like you?
                                            </legend>
                                            <div
                                                class="mt-4 grid gap-3 sm:grid-cols-2"
                                            >
                                                <button
                                                    v-for="o in options.stages"
                                                    :key="o.value"
                                                    type="button"
                                                    :class="[
                                                        cardBase,
                                                        form.stage === o.value
                                                            ? cardActive
                                                            : cardIdle,
                                                    ]"
                                                    :aria-pressed="
                                                        form.stage === o.value
                                                    "
                                                    @click="
                                                        pick('stage', o.value)
                                                    "
                                                >
                                                    <span
                                                        class="flex size-10 shrink-0 items-center justify-center rounded-xl transition"
                                                        :class="
                                                            form.stage ===
                                                            o.value
                                                                ? 'bg-marketing-orange text-white'
                                                                : 'bg-marketing-ink/5 text-marketing-ink group-hover:bg-marketing-amber/30'
                                                        "
                                                    >
                                                        <component
                                                            :is="icons[o.value]"
                                                            v-if="
                                                                icons[o.value]
                                                            "
                                                            class="size-5"
                                                        />
                                                    </span>
                                                    <span>
                                                        <span
                                                            class="block font-semibold"
                                                            >{{ o.label }}</span
                                                        >
                                                        <span
                                                            v-if="o.hint"
                                                            class="mt-0.5 block text-sm text-marketing-ink-muted"
                                                            >{{ o.hint }}</span
                                                        >
                                                    </span>
                                                    <Check
                                                        v-if="
                                                            form.stage ===
                                                            o.value
                                                        "
                                                        class="absolute top-3 right-3 size-4 text-marketing-orange"
                                                        stroke-width="3"
                                                    />
                                                </button>
                                            </div>
                                            <Transition name="fade">
                                                <p
                                                    v-if="reassurance"
                                                    class="mt-4 flex items-start gap-2 text-sm text-marketing-ink-muted"
                                                >
                                                    <Sparkles
                                                        class="mt-0.5 size-4 shrink-0 text-marketing-orange"
                                                    />
                                                    {{ reassurance }}
                                                </p>
                                            </Transition>
                                            <p
                                                v-if="form.errors.stage"
                                                class="mt-2 text-sm text-red-600"
                                            >
                                                {{ form.errors.stage }}
                                            </p>
                                        </fieldset>

                                        <Transition name="reveal">
                                            <div
                                                v-if="show.country"
                                                id="q-country"
                                                class="grid gap-6 sm:grid-cols-2"
                                            >
                                                <label class="block">
                                                    <span
                                                        class="text-lg font-semibold"
                                                        >Business name</span
                                                    >
                                                    <span
                                                        class="ml-1 text-sm text-marketing-ink-muted"
                                                        >{{
                                                            form.stage ===
                                                            'starting'
                                                                ? 'if you have one'
                                                                : 'optional'
                                                        }}</span
                                                    >
                                                    <input
                                                        v-model="
                                                            form.business_name
                                                        "
                                                        type="text"
                                                        maxlength="160"
                                                        autocomplete="organization"
                                                        placeholder="e.g. Island Express"
                                                        :class="inputClass"
                                                    />
                                                </label>
                                                <label class="block">
                                                    <span
                                                        class="text-lg font-semibold"
                                                        >Where are you
                                                        based?</span
                                                    >
                                                    <span
                                                        class="relative block"
                                                    >
                                                        <select
                                                            v-model="
                                                                form.country
                                                            "
                                                            :class="[
                                                                inputClass,
                                                                'appearance-none pr-10',
                                                                !form.country &&
                                                                    'text-marketing-ink-muted',
                                                            ]"
                                                            @change="
                                                                form.clearErrors(
                                                                    'country',
                                                                )
                                                            "
                                                        >
                                                            <option
                                                                value=""
                                                                disabled
                                                            >
                                                                Choose a country
                                                            </option>
                                                            <option
                                                                v-for="o in options.countries"
                                                                :key="o.value"
                                                                :value="o.value"
                                                            >
                                                                {{ o.label }}
                                                            </option>
                                                        </select>
                                                        <ChevronDown
                                                            class="pointer-events-none absolute top-1/2 right-4 mt-1 size-4 -translate-y-1/2 text-marketing-ink-muted"
                                                        />
                                                    </span>
                                                    <span
                                                        v-if="
                                                            form.errors.country
                                                        "
                                                        class="mt-2 block text-sm text-red-600"
                                                        >{{
                                                            form.errors.country
                                                        }}</span
                                                    >
                                                </label>
                                            </div>
                                        </Transition>

                                        <Transition name="reveal">
                                            <fieldset
                                                v-if="show.volume"
                                                id="q-volume"
                                            >
                                                <legend
                                                    class="text-lg font-semibold"
                                                >
                                                    Roughly how many packages a
                                                    month?
                                                </legend>
                                                <div
                                                    class="mt-4 flex flex-wrap gap-2"
                                                >
                                                    <button
                                                        v-for="o in options.volumes"
                                                        :key="o.value"
                                                        type="button"
                                                        :class="[
                                                            chipBase,
                                                            form.monthly_volume ===
                                                            o.value
                                                                ? chipActive
                                                                : chipIdle,
                                                        ]"
                                                        :aria-pressed="
                                                            form.monthly_volume ===
                                                            o.value
                                                        "
                                                        @click="
                                                            pick(
                                                                'monthly_volume',
                                                                o.value,
                                                            )
                                                        "
                                                    >
                                                        {{ o.label }}
                                                    </button>
                                                </div>
                                                <p
                                                    v-if="
                                                        form.errors
                                                            .monthly_volume
                                                    "
                                                    class="mt-2 text-sm text-red-600"
                                                >
                                                    {{
                                                        form.errors
                                                            .monthly_volume
                                                    }}
                                                </p>
                                            </fieldset>
                                        </Transition>

                                        <Transition name="reveal">
                                            <fieldset
                                                v-if="show.forwarder"
                                                id="q-forwarder"
                                            >
                                                <legend
                                                    class="text-lg font-semibold"
                                                >
                                                    How do packages get out of
                                                    the US?
                                                </legend>
                                                <p
                                                    class="mt-1 text-sm text-marketing-ink-muted"
                                                >
                                                    No wrong answer. If you need
                                                    a partner, we can help with
                                                    that too.
                                                </p>
                                                <div
                                                    class="mt-4 grid gap-3 sm:grid-cols-2"
                                                >
                                                    <button
                                                        v-for="o in options.forwarders"
                                                        :key="o.value"
                                                        type="button"
                                                        :class="[
                                                            cardBase,
                                                            'items-center',
                                                            form.forwarder ===
                                                            o.value
                                                                ? cardActive
                                                                : cardIdle,
                                                        ]"
                                                        :aria-pressed="
                                                            form.forwarder ===
                                                            o.value
                                                        "
                                                        @click="
                                                            pick(
                                                                'forwarder',
                                                                o.value,
                                                            )
                                                        "
                                                    >
                                                        <component
                                                            :is="icons[o.value]"
                                                            v-if="
                                                                icons[o.value]
                                                            "
                                                            class="size-5 shrink-0"
                                                            :class="
                                                                form.forwarder ===
                                                                o.value
                                                                    ? 'text-marketing-orange'
                                                                    : 'text-marketing-ink-muted'
                                                            "
                                                        />
                                                        <span
                                                            class="font-medium"
                                                            >{{ o.label }}</span
                                                        >
                                                    </button>
                                                </div>
                                                <p
                                                    v-if="form.errors.forwarder"
                                                    class="mt-2 text-sm text-red-600"
                                                >
                                                    {{ form.errors.forwarder }}
                                                </p>
                                            </fieldset>
                                        </Transition>
                                    </div>

                                    <!-- STEP 2 -->
                                    <div
                                        v-else-if="step === 1"
                                        key="s2"
                                        class="space-y-10"
                                    >
                                        <fieldset>
                                            <legend
                                                class="text-lg font-semibold"
                                            >
                                                What do you need CourierOS to
                                                do?
                                            </legend>
                                            <p
                                                class="mt-1 text-sm text-marketing-ink-muted"
                                            >
                                                Pick as many as you like.
                                                <span
                                                    v-if="form.needs.length"
                                                    class="font-semibold text-marketing-orange"
                                                    >{{
                                                        form.needs.length
                                                    }}
                                                    selected</span
                                                >
                                            </p>
                                            <div
                                                class="mt-4 grid gap-3 sm:grid-cols-3"
                                            >
                                                <button
                                                    v-for="o in options.needs"
                                                    :key="o.value"
                                                    type="button"
                                                    :class="[
                                                        cardBase,
                                                        'flex-col gap-2',
                                                        form.needs.includes(
                                                            o.value,
                                                        )
                                                            ? cardActive
                                                            : cardIdle,
                                                    ]"
                                                    :aria-pressed="
                                                        form.needs.includes(
                                                            o.value,
                                                        )
                                                    "
                                                    @click="toggleNeed(o.value)"
                                                >
                                                    <span
                                                        class="flex w-full items-center justify-between"
                                                    >
                                                        <component
                                                            :is="icons[o.value]"
                                                            v-if="
                                                                icons[o.value]
                                                            "
                                                            class="size-5"
                                                            :class="
                                                                form.needs.includes(
                                                                    o.value,
                                                                )
                                                                    ? 'text-marketing-orange'
                                                                    : 'text-marketing-ink-muted'
                                                            "
                                                        />
                                                        <span
                                                            class="flex size-5 items-center justify-center rounded-md border transition"
                                                            :class="
                                                                form.needs.includes(
                                                                    o.value,
                                                                )
                                                                    ? 'border-marketing-orange bg-marketing-orange text-white'
                                                                    : 'border-marketing-border'
                                                            "
                                                        >
                                                            <Check
                                                                v-if="
                                                                    form.needs.includes(
                                                                        o.value,
                                                                    )
                                                                "
                                                                class="size-3.5"
                                                                stroke-width="3"
                                                            />
                                                        </span>
                                                    </span>
                                                    <span
                                                        class="text-sm font-semibold"
                                                        >{{ o.label }}</span
                                                    >
                                                    <span
                                                        v-if="o.hint"
                                                        class="text-xs text-marketing-ink-muted"
                                                        >{{ o.hint }}</span
                                                    >
                                                </button>
                                            </div>
                                            <p
                                                v-if="fieldError('needs')"
                                                class="mt-2 text-sm text-red-600"
                                            >
                                                {{ fieldError('needs') }}
                                            </p>
                                        </fieldset>

                                        <Transition name="reveal">
                                            <fieldset
                                                v-if="show.timeline"
                                                id="q-timeline"
                                            >
                                                <legend
                                                    class="text-lg font-semibold"
                                                >
                                                    When would you like to be up
                                                    and running?
                                                </legend>
                                                <div
                                                    class="mt-4 flex flex-wrap gap-2"
                                                >
                                                    <button
                                                        v-for="o in options.timelines"
                                                        :key="o.value"
                                                        type="button"
                                                        :class="[
                                                            chipBase,
                                                            form.timeline ===
                                                            o.value
                                                                ? chipActive
                                                                : chipIdle,
                                                        ]"
                                                        :aria-pressed="
                                                            form.timeline ===
                                                            o.value
                                                        "
                                                        @click="
                                                            pick(
                                                                'timeline',
                                                                o.value,
                                                            )
                                                        "
                                                    >
                                                        {{ o.label }}
                                                    </button>
                                                </div>
                                                <p
                                                    v-if="form.errors.timeline"
                                                    class="mt-2 text-sm text-red-600"
                                                >
                                                    {{ form.errors.timeline }}
                                                </p>
                                            </fieldset>
                                        </Transition>

                                        <Transition name="reveal">
                                            <fieldset
                                                v-if="show.plan"
                                                id="q-plan"
                                            >
                                                <legend
                                                    class="text-lg font-semibold"
                                                >
                                                    How hands on do you want to
                                                    be?
                                                </legend>
                                                <div
                                                    class="mt-4 grid gap-3 sm:grid-cols-2"
                                                >
                                                    <button
                                                        v-for="o in options.plans"
                                                        :key="o.value"
                                                        type="button"
                                                        :class="[
                                                            cardBase,
                                                            form.plan_interest ===
                                                            o.value
                                                                ? cardActive
                                                                : cardIdle,
                                                        ]"
                                                        :aria-pressed="
                                                            form.plan_interest ===
                                                            o.value
                                                        "
                                                        @click="
                                                            pick(
                                                                'plan_interest',
                                                                o.value,
                                                            )
                                                        "
                                                    >
                                                        <span
                                                            class="flex size-10 shrink-0 items-center justify-center rounded-xl transition"
                                                            :class="
                                                                form.plan_interest ===
                                                                o.value
                                                                    ? 'bg-marketing-orange text-white'
                                                                    : 'bg-marketing-ink/5 text-marketing-ink'
                                                            "
                                                        >
                                                            <component
                                                                :is="
                                                                    icons[
                                                                        o.value
                                                                    ] ??
                                                                    CircleHelp
                                                                "
                                                                class="size-5"
                                                            />
                                                        </span>
                                                        <span>
                                                            <span
                                                                class="block font-semibold"
                                                                >{{
                                                                    o.label
                                                                }}</span
                                                            >
                                                            <span
                                                                v-if="o.hint"
                                                                class="mt-0.5 block text-sm text-marketing-ink-muted"
                                                                >{{
                                                                    o.hint
                                                                }}</span
                                                            >
                                                        </span>
                                                    </button>
                                                </div>
                                                <p
                                                    v-if="
                                                        form.errors
                                                            .plan_interest
                                                    "
                                                    class="mt-2 text-sm text-red-600"
                                                >
                                                    {{
                                                        form.errors
                                                            .plan_interest
                                                    }}
                                                </p>
                                            </fieldset>
                                        </Transition>

                                        <Transition name="reveal">
                                            <label
                                                v-if="show.challenge"
                                                id="q-challenge"
                                                class="block"
                                            >
                                                <span
                                                    class="text-lg font-semibold"
                                                    >What is the biggest
                                                    headache right now?</span
                                                >
                                                <span
                                                    class="ml-1 text-sm text-marketing-ink-muted"
                                                    >optional</span
                                                >
                                                <textarea
                                                    v-model="form.challenge"
                                                    rows="3"
                                                    maxlength="2000"
                                                    placeholder="e.g. Customers keep messaging to ask where their package is"
                                                    :class="[
                                                        inputClass,
                                                        'resize-none',
                                                    ]"
                                                />
                                                <span
                                                    class="mt-1 block text-right text-xs text-marketing-ink-muted"
                                                    >{{
                                                        form.challenge.length
                                                    }}/2000</span
                                                >
                                            </label>
                                        </Transition>
                                    </div>

                                    <!-- STEP 3 -->
                                    <div v-else key="s3" class="space-y-8">
                                        <fieldset>
                                            <legend
                                                class="text-lg font-semibold"
                                            >
                                                How should we get in touch?
                                            </legend>
                                            <div
                                                class="mt-4 grid grid-cols-3 gap-3"
                                            >
                                                <button
                                                    v-for="o in options.channels"
                                                    :key="o.value"
                                                    type="button"
                                                    :class="[
                                                        cardBase,
                                                        'flex-col items-center gap-2 py-5 text-center',
                                                        form.preferred_channel ===
                                                        o.value
                                                            ? cardActive
                                                            : cardIdle,
                                                    ]"
                                                    :aria-pressed="
                                                        form.preferred_channel ===
                                                        o.value
                                                    "
                                                    @click="
                                                        pick(
                                                            'preferred_channel',
                                                            o.value,
                                                        )
                                                    "
                                                >
                                                    <component
                                                        :is="icons[o.value]"
                                                        v-if="icons[o.value]"
                                                        class="size-6"
                                                        :class="
                                                            form.preferred_channel ===
                                                            o.value
                                                                ? 'text-marketing-orange'
                                                                : 'text-marketing-ink-muted'
                                                        "
                                                    />
                                                    <span
                                                        class="text-sm font-semibold"
                                                        >{{ o.label }}</span
                                                    >
                                                </button>
                                            </div>
                                            <p
                                                v-if="
                                                    form.errors
                                                        .preferred_channel
                                                "
                                                class="mt-2 text-sm text-red-600"
                                            >
                                                {{
                                                    form.errors
                                                        .preferred_channel
                                                }}
                                            </p>
                                        </fieldset>

                                        <div class="grid gap-5 sm:grid-cols-2">
                                            <label class="block">
                                                <span class="font-semibold"
                                                    >Your name</span
                                                >
                                                <input
                                                    v-model="form.name"
                                                    type="text"
                                                    autocomplete="name"
                                                    maxlength="120"
                                                    required
                                                    :class="inputClass"
                                                    @blur="touched.name = true"
                                                />
                                                <span
                                                    v-if="fieldError('name')"
                                                    class="mt-2 block text-sm text-red-600"
                                                    >{{
                                                        fieldError('name')
                                                    }}</span
                                                >
                                            </label>
                                            <label class="block">
                                                <span class="font-semibold"
                                                    >Email</span
                                                >
                                                <input
                                                    v-model="form.email"
                                                    type="email"
                                                    autocomplete="email"
                                                    maxlength="160"
                                                    required
                                                    :class="inputClass"
                                                    @blur="touched.email = true"
                                                />
                                                <span
                                                    v-if="fieldError('email')"
                                                    class="mt-2 block text-sm text-red-600"
                                                    >{{
                                                        fieldError('email')
                                                    }}</span
                                                >
                                            </label>
                                            <label class="block sm:col-span-2">
                                                <span class="font-semibold">{{
                                                    form.preferred_channel ===
                                                    'whatsapp'
                                                        ? 'WhatsApp number'
                                                        : 'Phone number'
                                                }}</span>
                                                <span
                                                    v-if="!needsPhone"
                                                    class="ml-1 text-sm text-marketing-ink-muted"
                                                    >optional</span
                                                >
                                                <input
                                                    v-model="form.phone"
                                                    type="tel"
                                                    autocomplete="tel"
                                                    maxlength="32"
                                                    placeholder="+1 876 555 0123"
                                                    :class="inputClass"
                                                    @blur="touched.phone = true"
                                                />
                                                <span
                                                    v-if="fieldError('phone')"
                                                    class="mt-2 block text-sm text-red-600"
                                                    >{{
                                                        fieldError('phone')
                                                    }}</span
                                                >
                                            </label>
                                        </div>

                                        <fieldset>
                                            <legend class="font-semibold">
                                                Best time to reach you
                                                <span
                                                    class="text-sm font-normal text-marketing-ink-muted"
                                                    >your local time</span
                                                >
                                            </legend>
                                            <div
                                                class="mt-3 flex flex-wrap gap-2"
                                            >
                                                <button
                                                    v-for="o in options.times"
                                                    :key="o.value"
                                                    type="button"
                                                    :class="[
                                                        chipBase,
                                                        form.best_time ===
                                                        o.value
                                                            ? chipActive
                                                            : chipIdle,
                                                    ]"
                                                    :aria-pressed="
                                                        form.best_time ===
                                                        o.value
                                                    "
                                                    :title="o.hint ?? undefined"
                                                    @click="
                                                        pick(
                                                            'best_time',
                                                            o.value,
                                                        )
                                                    "
                                                >
                                                    <component
                                                        :is="icons[o.value]"
                                                        v-if="icons[o.value]"
                                                        class="size-4"
                                                    />
                                                    {{ o.label }}
                                                </button>
                                            </div>
                                            <p
                                                v-if="form.errors.best_time"
                                                class="mt-2 text-sm text-red-600"
                                            >
                                                {{ form.errors.best_time }}
                                            </p>
                                        </fieldset>

                                        <p
                                            class="text-xs text-marketing-ink-muted"
                                        >
                                            We only use these details to reply
                                            to you. See our
                                            <a
                                                href="/legal/privacy"
                                                class="underline underline-offset-2"
                                                >privacy policy</a
                                            >.
                                        </p>
                                    </div>
                                </Transition>
                            </div>

                            <!-- Footer controls -->
                            <div
                                class="flex items-center justify-between gap-3 border-t border-marketing-border bg-white px-5 py-4 sm:px-8"
                            >
                                <button
                                    v-if="step > 0"
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-marketing-ink-muted transition hover:text-marketing-ink"
                                    @click="back"
                                >
                                    <ArrowLeft class="size-4" />
                                    Back
                                </button>
                                <span
                                    v-else
                                    class="text-xs text-marketing-ink-muted"
                                    >Step 1 of 3</span
                                >

                                <button
                                    v-if="step < 2"
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-xl px-6 py-3 text-sm font-semibold transition duration-200"
                                    :class="
                                        stepValid[step]
                                            ? 'bg-marketing-ink text-marketing-cream hover:-translate-y-0.5 hover:shadow-lg'
                                            : 'bg-marketing-ink/10 text-marketing-ink-muted'
                                    "
                                    :aria-disabled="!stepValid[step]"
                                    @click="next"
                                >
                                    Continue
                                    <ArrowRight class="size-4" />
                                </button>
                                <button
                                    v-else
                                    type="submit"
                                    :disabled="form.processing"
                                    class="cta-gradient inline-flex items-center gap-2 rounded-xl px-6 py-3 text-sm font-semibold text-white shadow-[0_14px_40px_-16px_rgba(226,83,10,0.8)] transition duration-200 hover:-translate-y-0.5 disabled:opacity-70"
                                    :class="!stepValid[2] && 'opacity-60'"
                                >
                                    <LoaderCircle
                                        v-if="form.processing"
                                        class="size-4 animate-spin"
                                    />
                                    <Send v-else class="size-4" />
                                    {{
                                        form.processing
                                            ? 'Sending'
                                            : 'Send my brief'
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sidebar: the brief building live, plus direct options -->
                <aside class="space-y-5 lg:col-span-4">
                    <div
                        class="rounded-3xl border border-marketing-border bg-white p-6 lg:sticky lg:top-24"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold">Your brief</p>
                            <span
                                class="rounded-full bg-marketing-ink/5 px-2.5 py-1 text-xs font-semibold tabular-nums"
                                >{{ progress }}%</span
                            >
                        </div>
                        <p class="mt-1 text-xs text-marketing-ink-muted">
                            This is what lands with us when you hit send.
                        </p>

                        <dl class="mt-5 space-y-3 text-sm">
                            <template v-for="row in brief" :key="row.label">
                                <div
                                    v-if="!row.optional || row.value"
                                    class="flex items-start justify-between gap-4"
                                >
                                    <dt
                                        class="shrink-0 text-marketing-ink-muted"
                                    >
                                        {{ row.label }}
                                    </dt>
                                    <dd class="text-right">
                                        <Transition name="fade" mode="out-in">
                                            <span
                                                v-if="row.value"
                                                :key="row.value"
                                                class="font-medium"
                                                >{{ row.value }}</span
                                            >
                                            <span
                                                v-else
                                                class="inline-block h-3 w-16 translate-y-0.5 rounded-full bg-marketing-ink/5"
                                            />
                                        </Transition>
                                    </dd>
                                </div>
                            </template>
                            <div>
                                <dt class="text-marketing-ink-muted">Needs</dt>
                                <dd class="mt-2 flex flex-wrap gap-1.5">
                                    <TransitionGroup name="chip">
                                        <span
                                            v-for="n in briefNeeds"
                                            :key="n"
                                            class="rounded-full bg-marketing-amber/25 px-2.5 py-1 text-xs font-medium"
                                            >{{ n }}</span
                                        >
                                    </TransitionGroup>
                                    <span
                                        v-if="!briefNeeds.length"
                                        class="inline-block h-5 w-24 rounded-full bg-marketing-ink/5"
                                    />
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div
                        class="rounded-3xl bg-marketing-ink p-6 text-marketing-cream"
                    >
                        <p class="text-sm font-semibold">Rather just talk?</p>
                        <p class="mt-1 text-sm text-marketing-cream/70">
                            Message us directly or look around a working courier
                            site first.
                        </p>
                        <div class="mt-5 space-y-2.5">
                            <a
                                v-if="salesChat"
                                :href="salesChat"
                                target="_blank"
                                rel="noopener"
                                class="flex items-center gap-3 rounded-xl bg-white/10 px-4 py-3 text-sm font-semibold transition hover:bg-white/15"
                                @click="
                                    trackMarketing('Contact', {
                                        content_name:
                                            'contact_sidebar_whatsapp',
                                    })
                                "
                            >
                                <MessageCircle
                                    class="size-4 text-marketing-amber"
                                />
                                WhatsApp us
                                <ArrowRight class="ml-auto size-4 opacity-60" />
                            </a>
                            <a
                                v-if="liveDemo"
                                :href="liveDemo.url"
                                target="_blank"
                                rel="noopener"
                                class="flex items-center gap-3 rounded-xl bg-white/10 px-4 py-3 text-sm font-semibold transition hover:bg-white/15"
                            >
                                <CirclePlay
                                    class="size-4 text-marketing-amber"
                                />
                                See {{ liveDemo.name }}
                                <ArrowRight class="ml-auto size-4 opacity-60" />
                            </a>
                            <a
                                href="/pricing"
                                class="flex items-center gap-3 rounded-xl bg-white/10 px-4 py-3 text-sm font-semibold transition hover:bg-white/15"
                            >
                                <CreditCard
                                    class="size-4 text-marketing-amber"
                                />
                                View pricing
                                <ArrowRight class="ml-auto size-4 opacity-60" />
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <MarketingFooter />
    </div>
</template>

<style scoped>
.progress-fill,
.cta-gradient {
    background: linear-gradient(
        90deg,
        var(--marketing-orange-deep, #e2530a),
        var(--marketing-orange, #ff7a00),
        var(--marketing-amber, #ffb703)
    );
}

.step-forward-enter-active,
.step-forward-leave-active,
.step-back-enter-active,
.step-back-leave-active {
    transition:
        opacity 0.28s ease,
        transform 0.28s ease;
}
.step-forward-enter-from,
.step-back-leave-to {
    opacity: 0;
    transform: translateX(24px);
}
.step-forward-leave-to,
.step-back-enter-from {
    opacity: 0;
    transform: translateX(-24px);
}

.reveal-enter-active {
    transition:
        opacity 0.4s ease,
        transform 0.4s ease;
}
.reveal-enter-from {
    opacity: 0;
    transform: translateY(12px);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.chip-enter-active,
.chip-leave-active {
    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}
.chip-enter-from,
.chip-leave-to {
    opacity: 0;
    transform: scale(0.85);
}

.success-in {
    animation: success-in 0.5s ease both;
}
.check-pop {
    animation: check-pop 0.5s 0.15s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}
@keyframes success-in {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
}
@keyframes check-pop {
    from {
        opacity: 0;
        transform: scale(0.4);
    }
}

@media (prefers-reduced-motion: reduce) {
    .step-forward-enter-active,
    .step-forward-leave-active,
    .step-back-enter-active,
    .step-back-leave-active,
    .reveal-enter-active,
    .chip-enter-active,
    .chip-leave-active,
    .success-in,
    .check-pop {
        transition: none;
        animation: none;
    }
}
</style>
