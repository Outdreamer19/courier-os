<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

const props = defineProps<{
    currencies: string[];
    pricing: { monthly: number; setup: number; currency: string };
}>();

const centralDomain = computed(() => {
    if (typeof window === 'undefined') {
        return 'courieros.co';
    }
    const parts = window.location.host.split('.');
    return parts.slice(-2).join('.');
});
</script>

<template>
    <Head title="Start your courier platform — CourierOS" />

    <div class="min-h-screen bg-[hsl(222_47%_11%)] text-white lg:grid lg:grid-cols-2">
        <!-- Left: the pitch -->
        <section
            class="relative hidden flex-col justify-between overflow-hidden px-12 py-14 lg:flex"
        >
            <div
                class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-[hsl(168_76%_42%)] opacity-20 blur-3xl"
            />
            <div
                class="pointer-events-none absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-[hsl(214_90%_60%)] opacity-15 blur-3xl"
            />

            <div class="relative flex items-center gap-2.5">
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-[hsl(168_76%_42%)] font-bold text-[hsl(222_47%_11%)]"
                >
                    C
                </div>
                <span class="text-lg font-semibold tracking-tight">CourierOS</span>
            </div>

            <div class="relative max-w-md">
                <p
                    class="mb-4 text-sm font-medium uppercase tracking-[0.2em] text-[hsl(168_76%_55%)]"
                >
                    Built for Caribbean couriers
                </p>
                <h1 class="text-4xl font-semibold leading-[1.1] tracking-tight">
                    Run your whole shipping operation from one place.
                </h1>
                <p class="mt-5 text-base leading-relaxed text-white/70">
                    Pre-alerts, package tracking, billing and customer pickups —
                    on your own branded site. Launch in a day, not months.
                </p>

                <ul class="mt-8 space-y-3 text-sm text-white/80">
                    <li class="flex items-center gap-3">
                        <span class="text-[hsl(168_76%_55%)]">●</span>
                        Your own subdomain and branding
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="text-[hsl(168_76%_55%)]">●</span>
                        Customer accounts, pre-alerts and tracking
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="text-[hsl(168_76%_55%)]">●</span>
                        Online invoicing and WhatsApp updates
                    </li>
                </ul>
            </div>

            <div class="relative text-sm text-white/50">
                Trusted by growing courier businesses across Jamaica.
            </div>
        </section>

        <!-- Right: the form -->
        <section
            class="flex items-center justify-center bg-[hsl(40_30%_99%)] px-6 py-12 text-[hsl(222_47%_11%)] lg:px-12"
        >
            <div class="w-full max-w-md">
                <div class="mb-8">
                    <h2 class="text-2xl font-semibold tracking-tight">
                        Create your courier platform
                    </h2>
                    <p class="mt-2 text-sm text-muted-foreground">
                        {{ pricing.currency }} ${{ pricing.monthly }}/month + ${{
                            pricing.setup
                        }}
                        one-time setup. Cancel anytime.
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
                                class="flex items-center whitespace-nowrap bg-muted px-3 text-sm text-muted-foreground"
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
                                class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-ring"
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
                        class="mt-2 h-11 bg-[hsl(168_76%_38%)] text-white hover:bg-[hsl(168_76%_32%)]"
                    >
                        <Spinner v-if="processing" class="mr-2" />
                        Continue to payment
                    </Button>

                    <p class="text-center text-xs text-muted-foreground">
                        You'll confirm payment securely with Stripe on the next
                        step.
                    </p>
                </Form>
            </div>
        </section>
    </div>
</template>
