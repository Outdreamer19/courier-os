<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Calendar, IdCard, Lock, Mail, MapPin, Phone, User } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    passwordRules: string;
}>();

defineOptions({
    layout: {
        title: 'Create an account',
        description: 'Enter your details below to create your account',
    },
});
</script>

<template>
    <Head title="Register" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <div class="relative">
                    <User
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        placeholder="Full name"
                        class="pl-9"
                    />
                </div>
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <div class="relative">
                    <Mail
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        name="email"
                        placeholder="email@example.com"
                        class="pl-9"
                    />
                </div>
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="trn">TRN (Tax Registration Number)</Label>
                <div class="relative">
                    <IdCard
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        id="trn"
                        type="text"
                        required
                        :tabindex="3"
                        name="trn"
                        placeholder="Your Jamaica TRN"
                        class="pl-9"
                    />
                </div>
                <InputError :message="errors.trn" />
            </div>

            <div class="grid gap-2">
                <Label for="phone">Phone number</Label>
                <div class="relative">
                    <Phone
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        id="phone"
                        type="tel"
                        required
                        :tabindex="4"
                        autocomplete="tel"
                        name="phone"
                        placeholder="+1 (876) 555-0100"
                        class="pl-9"
                    />
                </div>
                <InputError :message="errors.phone" />
            </div>

            <div class="grid gap-2">
                <Label for="jamaica_address">Address</Label>
                <div class="relative">
                    <MapPin
                        class="pointer-events-none absolute top-3 left-3 size-4 text-muted-foreground"
                    />
                    <textarea
                        id="jamaica_address"
                        name="jamaica_address"
                        rows="3"
                        required
                        :tabindex="5"
                        class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 pl-9 text-sm shadow-xs"
                        placeholder="Your Jamaica address"
                    />
                </div>
                <InputError :message="errors.jamaica_address" />
            </div>

            <div class="grid gap-2">
                <Label for="date_of_birth">Date of birth</Label>
                <div class="relative">
                    <Calendar
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        id="date_of_birth"
                        type="date"
                        required
                        :tabindex="6"
                        name="date_of_birth"
                        class="pl-9"
                    />
                </div>
                <InputError :message="errors.date_of_birth" />
                <p class="text-xs text-muted-foreground">
                    You must be at least 18 years old to register.
                </p>
            </div>

            <div class="grid gap-2">
                <Label for="password">Password</Label>
                <div class="relative">
                    <Lock
                        class="pointer-events-none absolute top-1/2 left-3 z-10 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <PasswordInput
                        id="password"
                        required
                        :tabindex="7"
                        autocomplete="new-password"
                        name="password"
                        placeholder="Password"
                        :passwordrules="passwordRules"
                        class="pl-9"
                    />
                </div>
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Confirm password</Label>
                <div class="relative">
                    <Lock
                        class="pointer-events-none absolute top-1/2 left-3 z-10 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <PasswordInput
                        id="password_confirmation"
                        required
                        :tabindex="8"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Confirm password"
                        :passwordrules="passwordRules"
                        class="pl-9"
                    />
                </div>
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-2 w-full"
                tabindex="9"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                Create account
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Already have an account?
            <TextLink
                :href="login()"
                class="underline underline-offset-4"
                :tabindex="10"
                >Log in</TextLink
            >
        </div>
    </Form>
</template>
