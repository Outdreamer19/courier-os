<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Mail, MessageCircle, Phone, Send } from 'lucide-vue-next';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { store as contactStore } from '@/routes/contact';

const page = usePage();

const form = useForm({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
});

const successMessage = computed(() => page.props.flash?.success ?? null);

watch(
    successMessage,
    (value) => {
        if (value) {
            toast.success(value);
            form.reset();
        }
    },
    { immediate: true },
);

const submit = () => {
    form.post(contactStore().url, {
        preserveScroll: true,
    });
};

const { sectionDelay, itemDelay } = useScrollReveal();
</script>

<template>
    <Head title="Contact SHIP DJM" />

    <section class="relative overflow-hidden bg-brand-ink text-brand-cream fade-in-section" :style="sectionDelay(0)">
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_75%_25%,rgba(30,142,62,0.18),transparent_50%)]"
            aria-hidden="true"
        />
        <div class="relative mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
            <p class="public-section-label-on-dark">
                Contact us
            </p>
            <h1
                class="mt-3 text-balance text-4xl font-semibold tracking-tight sm:text-5xl"
            >
                We'd love to hear from you
            </h1>
            <p class="mt-4 max-w-2xl text-brand-cream/80">
                Questions about a package, a pre-alert, payments, or general
                feedback? Send a note below and our team will be in touch.
            </p>
        </div>
    </section>

    <section class="bg-background fade-in-section" :style="sectionDelay(1)">
        <div
            class="mx-auto grid max-w-6xl gap-8 px-4 py-16 sm:px-6 lg:grid-cols-[1.4fr_1fr] lg:px-8"
        >
            <Card class="fade-in-item" :style="itemDelay(0)">
                <CardHeader>
                    <CardTitle>Send a message</CardTitle>
                    <CardDescription>
                        We read every message. Required fields are marked with
                        an asterisk.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form class="space-y-5" @submit.prevent="submit">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Name *</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    required
                                    autocomplete="name"
                                />
                                <InputError :message="form.errors.name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="email">Email *</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    autocomplete="email"
                                />
                                <InputError :message="form.errors.email" />
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="phone">Phone (optional)</Label>
                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    autocomplete="tel"
                                />
                                <InputError :message="form.errors.phone" />
                            </div>
                            <div class="space-y-2">
                                <Label for="subject">Subject *</Label>
                                <Input
                                    id="subject"
                                    v-model="form.subject"
                                    required
                                />
                                <InputError :message="form.errors.subject" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="message">Message *</Label>
                            <textarea
                                id="message"
                                v-model="form.message"
                                rows="6"
                                required
                                class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            ></textarea>
                            <InputError :message="form.errors.message" />
                        </div>

                        <div class="flex items-center justify-end">
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="public-cta"
                            >
                                <Send class="size-4" />
                                {{ form.processing ? 'Sending…' : 'Send message' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <div class="space-y-6">
                <Card class="fade-in-item" :style="itemDelay(1)">
                    <CardHeader>
                        <CardTitle>Other ways to reach us</CardTitle>
                        <CardDescription>
                            Placeholders for now — we'll publish official
                            details once the operations launch is finalised.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm">
                        <div class="flex items-start gap-3">
                            <Mail class="mt-0.5 size-4 text-brand-green" />
                            <div>
                                <p class="font-medium">Email</p>
                                <p class="text-muted-foreground">
                                    support@shipdjm.com (placeholder)
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <Phone class="mt-0.5 size-4 text-brand-green" />
                            <div>
                                <p class="font-medium">Phone</p>
                                <p class="text-muted-foreground">
                                    Coming soon
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <MessageCircle
                                class="mt-0.5 size-4 text-brand-green"
                            />
                            <div>
                                <p class="font-medium">WhatsApp</p>
                                <p class="text-muted-foreground">
                                    Click-to-chat coming once the official
                                    number is confirmed.
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div
                    class="rounded-xl border border-border bg-secondary/30 p-6 text-sm text-muted-foreground fade-in-item"
                    :style="itemDelay(2)"
                >
                    Already a customer? Log in to send a support message tied
                    directly to your account and packages.
                </div>
            </div>
        </div>
    </section>
</template>
