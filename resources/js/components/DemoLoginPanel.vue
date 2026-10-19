<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ArrowRight, Package, ShieldCheck, Sparkles } from 'lucide-vue-next';
import { computed } from 'vue';
import { Spinner } from '@/components/ui/spinner';

/**
 * One tap logins for the public demo. Posts the public demo credentials to
 * the real login endpoint, so visitors go through exactly the same sign in a
 * courier's customers would, without ever typing an email or password.
 *
 * On the demo courier this panel IS the login page: Login.vue hides the
 * email and password form there so nobody thinks they need an account.
 */
const props = defineProps<{
    /** Key of the view currently signing in, so its card can show a spinner. */
    loading?: string | null;
}>();

const emit = defineEmits<{
    (
        e: 'login',
        key: string,
        credentials: { email: string; password: string },
    ): void;
}>();

const page = usePage();
const demo = computed(() => page.props.demo ?? null);
const error = computed(
    () => (page.props.errors as Record<string, string> | undefined)?.email,
);

const accounts = computed(() =>
    demo.value
        ? [
              {
                  key: 'customer',
                  title: 'Customer view',
                  who: 'See it as a shopper',
                  body: 'Their own Miami address, pre-alerts, package tracking and what they owe.',
                  cta: 'Open customer view',
                  email: demo.value.customer_email,
                  icon: Package,
              },
              {
                  key: 'admin',
                  title: 'Courier admin view',
                  who: 'See it as the business owner',
                  body: 'Receive packages, match pre-alerts, update statuses and see your reports.',
                  cta: 'Open admin view',
                  email: demo.value.admin_email,
                  icon: ShieldCheck,
              },
          ]
        : [],
);

const busy = computed(() => Boolean(props.loading));
</script>

<template>
    <section
        v-if="demo"
        class="relative overflow-hidden rounded-2xl border-2 border-primary/40 bg-gradient-to-br from-primary/[0.09] via-primary/[0.04] to-transparent p-5 shadow-[0_18px_50px_-24px_color-mix(in_oklab,var(--primary)_55%,transparent)] sm:p-6"
        aria-labelledby="demo-login-heading"
    >
        <span
            class="inline-flex items-center gap-1.5 rounded-full bg-primary px-2.5 py-1 text-[11px] font-semibold tracking-wide text-primary-foreground uppercase"
        >
            <Sparkles class="size-3.5" aria-hidden="true" />
            No account needed
        </span>

        <h2
            id="demo-login-heading"
            class="mt-3 text-xl font-semibold tracking-tight text-foreground"
        >
            Try the demo in one tap
        </h2>
        <p class="mt-1 text-sm text-muted-foreground">
            Pick a view below and you are signed straight in. No email, no
            password, nothing to fill in.
        </p>

        <div class="mt-5 grid gap-3">
            <button
                v-for="account in accounts"
                :key="account.key"
                type="button"
                :disabled="busy"
                :aria-busy="loading === account.key"
                :data-test="`demo-login-${account.key}`"
                class="group relative flex w-full cursor-pointer items-start gap-4 rounded-xl border-2 border-border bg-background p-4 text-left shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-primary hover:shadow-md focus-visible:border-primary focus-visible:ring-4 focus-visible:ring-primary/25 focus-visible:outline-none active:translate-y-0 disabled:cursor-wait disabled:opacity-70 disabled:hover:translate-y-0"
                @click="
                    emit('login', account.key, {
                        email: account.email,
                        password: demo!.password,
                    })
                "
            >
                <span
                    class="hidden size-11 shrink-0 place-items-center rounded-xl bg-primary/10 text-primary transition group-hover:bg-primary group-hover:text-primary-foreground sm:grid"
                >
                    <component
                        :is="account.icon"
                        class="size-5"
                        aria-hidden="true"
                    />
                </span>

                <span class="min-w-0 flex-1">
                    <span
                        class="block text-[11px] font-medium tracking-wide text-muted-foreground uppercase"
                        >{{ account.who }}</span
                    >
                    <span class="block text-base font-semibold text-foreground">
                        {{ account.title }}
                    </span>
                    <span
                        class="mt-0.5 block text-sm leading-snug text-muted-foreground"
                        >{{ account.body }}</span
                    >
                    <span
                        class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-primary px-3 py-1.5 text-sm font-semibold whitespace-nowrap text-primary-foreground transition group-hover:gap-2.5"
                    >
                        <Spinner
                            v-if="loading === account.key"
                            class="size-4"
                        />
                        {{
                            loading === account.key
                                ? 'Signing you in...'
                                : account.cta
                        }}
                        <ArrowRight
                            v-if="loading !== account.key"
                            class="size-4"
                            aria-hidden="true"
                        />
                    </span>
                </span>
            </button>
        </div>

        <p
            v-if="error"
            class="mt-3 text-sm font-medium text-destructive"
            role="alert"
        >
            {{ error }}
        </p>
    </section>
</template>
