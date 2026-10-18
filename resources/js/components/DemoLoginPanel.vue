<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Package, ShieldCheck } from 'lucide-vue-next';
import { computed } from 'vue';

/**
 * One tap logins for the public demo. Fills the real login form and submits
 * it, so visitors go through exactly the same sign in a courier's customers
 * would.
 */
const emit = defineEmits<{
    (e: 'login', credentials: { email: string; password: string }): void;
}>();

const page = usePage();
const demo = computed(() => page.props.demo ?? null);

const accounts = computed(() =>
    demo.value
        ? [
              {
                  key: 'customer',
                  title: 'Customer view',
                  body: 'Miami address, pre-alerts, tracking and what is owed.',
                  email: demo.value.customer_email,
                  icon: Package,
              },
              {
                  key: 'admin',
                  title: 'Courier admin view',
                  body: 'Receive packages, match pre-alerts, update statuses, reports.',
                  email: demo.value.admin_email,
                  icon: ShieldCheck,
              },
          ]
        : [],
);
</script>

<template>
    <section
        v-if="demo"
        class="mb-6 rounded-2xl border border-[#1F5F6B]/25 bg-[#1F5F6B]/[0.06] p-4"
        aria-labelledby="demo-login-heading"
    >
        <h2
            id="demo-login-heading"
            class="text-sm font-semibold text-foreground"
        >
            Try the demo in one tap
        </h2>
        <p class="mt-1 text-xs text-muted-foreground">
            Pick a view. Password for both:
            <code class="rounded bg-background px-1.5 py-0.5 font-mono">{{
                demo.password
            }}</code>
        </p>

        <div class="mt-3 grid gap-2">
            <button
                v-for="account in accounts"
                :key="account.key"
                type="button"
                :data-test="`demo-login-${account.key}`"
                class="group flex items-start gap-3 rounded-xl border border-border bg-background p-3 text-left transition hover:border-[#1F5F6B] hover:shadow-sm focus-visible:ring-2 focus-visible:ring-[#1F5F6B] focus-visible:outline-none"
                @click="
                    emit('login', {
                        email: account.email,
                        password: demo.password,
                    })
                "
            >
                <component
                    :is="account.icon"
                    class="mt-0.5 size-5 shrink-0 text-[#1F5F6B]"
                />
                <span class="min-w-0">
                    <span class="block text-sm font-semibold">{{
                        account.title
                    }}</span>
                    <span class="block text-xs text-muted-foreground">{{
                        account.body
                    }}</span>
                    <span
                        class="mt-1 block truncate font-mono text-[11px] text-muted-foreground"
                        >{{ account.email }}</span
                    >
                </span>
            </button>
        </div>
    </section>
</template>
