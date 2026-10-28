<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ArrowRight, Check } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

defineProps<{
    currencies: string[];
    pricing: {
        monthly: number;
        setup: number;
        currency: string;
        founding_setup?: number | null;
    };
    centralDomain: string;
    liveDemoUrl?: string | null;
}>();
</script>

<template>
    <Head title="Start your courier platform" />

    <!--
        CourierOS palette (warm ink, cream, orange and amber) to match the
        marketing site. `.courieros-brand` maps the shadcn tokens used by the
        form controls onto the same palette.
    -->
    <div
        class="courieros-brand min-h-screen bg-marketing-ink font-marketing text-white lg:grid lg:grid-cols-2"
    >
        <!-- Left: the pitch -->
        <section
            class="relative hidden flex-col justify-between overflow-hidden px-12 py-14 lg:flex"
        >
            <div
                class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-marketing-orange opacity-25 blur-3xl"
            />
            <div
                class="pointer-events-none absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-marketing-amber opacity-15 blur-3xl"
            />

            <a href="/" class="relative flex items-center gap-2.5">
                <span
                    class="flex size-9 items-center justify-center rounded-lg bg-marketing-amber font-bold text-marketing-ink"
                >
                    C
                </span>
                <span class="text-lg font-semibold tracking-tight"
                    >CourierOS</span
                >
            </a>

            <div class="relative max-w-md">
                <p
                    class="mb-4 text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase"
                >
                    Built for Caribbean couriers
                </p>
                <h1 class="text-4xl leading-[1.1] font-semibold tracking-tight">
                    Run your courier business
                    <span class="text-marketing-amber">without the chaos.</span>
                </h1>
                <p class="mt-5 text-base leading-relaxed text-white/70">
                    Your customers get their own Florida address, send pre-alerts
                    and track every package on your branded site. You receive,
                    bill and update them from one dashboard.
                </p>

                <ul class="mt-8 space-y-3 text-sm text-white/85">
                    <li
                        v-for="point in [
                            'Your branded site is live in minutes',
                            'Customer accounts, pre-alerts and tracking',
                            'Online payments and WhatsApp updates',
                        ]"
                        :key="point"
                        class="flex items-center gap-3"
                    >
                        <span
                            class="flex size-5 items-center justify-center rounded-full bg-marketing-orange/20 text-marketing-orange"
                        >
                            <Check class="size-3.5" aria-hidden="true" />
                        </span>
                        {{ point }}
                    </li>
                </ul>
            </div>

            <a
                v-if="liveDemoUrl"
                :href="liveDemoUrl"
                class="relative inline-flex w-fit items-center gap-2 text-sm text-white/60 transition hover:text-white"
            >
                Not ready yet? Click around the live demo first
                <ArrowRight class="size-4" aria-hidden="true" />
            </a>
        </section>

        <!-- Right: the form -->
        <section
            class="flex min-h-screen items-center justify-center bg-marketing-cream px-6 py-12 text-marketing-ink lg:px-12"
        >
            <div class="w-full max-w-md">
                <!-- Brand mark for phones, where the left panel is hidden -->
                <a href="/" class="mb-8 flex items-center gap-2.5 lg:hidden">
                    <span
                        class="flex size-8 items-center justify-center rounded-lg bg-marketing-ink text-sm font-bold text-marketing-amber"
                        >C</span
                    >
                    <span class="text-lg font-semibold tracking-tight"
                        >CourierOS</span
                    >
                </a>
                <div class="mb-8">
                    <p class="mb-3 text-right text-sm text-muted-foreground">
                        Have an account?
                        <a
                            href="/login"
                            class="font-semibold text-marketing-ink underline-offset-4 hover:underline"
                            >Log in</a
                        >
                    </p>
                    <h2 class="text-2xl font-semibold tracking-tight">
                        Create your courier site
                    </h2>
                    <p class="mt-2 text-sm text-muted-foreground">
                        US${{ pricing.monthly }} a month +
                        <template v-if="pricing.founding_setup">
                            <span class="line-through"
                                >US${{ pricing.setup }}</span
                            >{{ ' ' }}
                            <strong class="text-foreground"
                                >US${{ pricing.founding_setup }}</strong
                            >
                            one-time setup (founding price).
                        </template>
                        <template v-else
                            >US${{ pricing.setup }} one-time setup.</template
                        >
                        Cancel anytime.
                    </p>
                </div>

                <Form
                    action="/signup"
                    method="post"
                    :reset-on-success="['password', 'password_confirmation']"
                    v-slot="{ errors, processing }"
                    class="flex flex-col gap-5"
                >
                    <div class="grid gap-2">
                        <Label for="business_name">Business name</Label>
                        <Input
                            id="business_name"
                            name="business_name"
                            type="text"
                            required
                            autofocus
                            placeholder="Acme Courier"
                        />
                        <InputError :message="errors.business_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="subdomain">Choose your web address</Label>
                        <div
                            class="flex items-stretch overflow-hidden rounded-md border border-input bg-background focus-within:ring-2 focus-within:ring-ring"
                        >
                            <Input
                                id="subdomain"
                                name="subdomain"
                                type="text"
                                required
                                placeholder="acme"
                                class="border-0 focus-visible:ring-0"
                            />
                            <span
                                class="flex items-center bg-muted px-3 text-sm whitespace-nowrap text-muted-foreground"
                            >
                                .{{ centralDomain }}
                            </span>
                        </div>
                        <InputError :message="errors.subdomain" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="currency">Currency</Label>
                            <select
                                id="currency"
                                name="currency"
                                required
                                class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-sm focus:ring-2 focus:ring-ring focus:outline-none"
                            >
                                <option
                                    v-for="c in currencies"
                                    :key="c"
                                    :value="c"
                                >
                                    {{ c }}
                                </option>
                            </select>
                            <InputError :message="errors.currency" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="customer_reference_prefix">
                                Customer ref prefix
                            </Label>
                            <Input
                                id="customer_reference_prefix"
                                name="customer_reference_prefix"
                                type="text"
                                required
                                placeholder="ACM"
                                maxlength="8"
                                class="uppercase"
                            />
                            <InputError
                                :message="errors.customer_reference_prefix"
                            />
                        </div>
                    </div>

                    <div class="my-1 h-px bg-border" />

                    <div class="grid gap-2">
                        <Label for="owner_name">Your name</Label>
                        <Input
                            id="owner_name"
                            name="owner_name"
                            type="text"
                            required
                            autocomplete="name"
                            placeholder="Jane Brown"
                        />
                        <InputError :message="errors.owner_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="owner_email">Email address</Label>
                        <Input
                            id="owner_email"
                            name="owner_email"
                            type="email"
                            required
                            autocomplete="email"
                            placeholder="you@business.com"
                        />
                        <InputError :message="errors.owner_email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Password</Label>
                        <PasswordInput
                            id="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="Create a strong password"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">
                            Confirm password
                        </Label>
                        <PasswordInput
                            id="password_confirmation"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Re-enter your password"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <Button
                        type="submit"
                        :disabled="processing"
                        class="mt-2 h-11 bg-marketing-ink text-marketing-cream shadow-lg shadow-marketing-ink/15 hover:bg-marketing-ink/90"
                    >
                        <Spinner v-if="processing" class="mr-2" />
                        Continue to payment
                    </Button>

                    <p class="text-center text-xs text-muted-foreground">
                        You'll confirm payment securely with Stripe on the next
                        step. By continuing you agree to our
                        <a
                            href="/legal/terms"
                            target="_blank"
                            class="underline underline-offset-2 hover:text-marketing-ink"
                            >Terms of Service</a
                        >
                        and
                        <a
                            href="/legal/privacy"
                            target="_blank"
                            class="underline underline-offset-2 hover:text-marketing-ink"
                            >Privacy Policy</a
                        >.
                    </p>
                    <p class="text-center text-sm text-muted-foreground">
                        Already have an account?
                        <a
                            href="/login"
                            class="font-semibold text-marketing-ink underline underline-offset-4 hover:text-marketing-orange"
                            >Log in</a
                        >
                    </p>
                </Form>
            </div>
        </section>
    </div>
</template>
