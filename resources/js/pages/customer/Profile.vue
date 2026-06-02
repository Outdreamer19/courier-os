<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import { edit, update } from '@/routes/portal/profile';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'My profile', href: edit() },
        ],
    },
});

const props = defineProps<{
    profile: {
        customer_reference: string;
        phone: string | null;
        whatsapp_number: string | null;
        jamaica_address: string | null;
        parish: string | null;
    };
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <Head title="My profile" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <Heading
            title="My profile"
            description="Update your contact details and Jamaica address. Your customer reference cannot be changed."
        />

        <p class="text-sm text-muted-foreground">
            Customer reference:
            <span class="font-medium text-foreground">
                {{ profile.customer_reference }}
            </span>
        </p>

        <Form
            v-bind="update.form()"
            class="max-w-2xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Full name</Label>
                <Input
                    id="name"
                    name="name"
                    :default-value="user?.name"
                    required
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    name="email"
                    type="email"
                    :default-value="user?.email"
                    required
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="phone">Phone</Label>
                <Input
                    id="phone"
                    name="phone"
                    :default-value="profile.phone ?? ''"
                />
                <InputError :message="errors.phone" />
            </div>

            <div class="grid gap-2">
                <Label for="whatsapp_number">WhatsApp number</Label>
                <Input
                    id="whatsapp_number"
                    name="whatsapp_number"
                    :default-value="profile.whatsapp_number ?? ''"
                />
                <InputError :message="errors.whatsapp_number" />
            </div>

            <div class="grid gap-2">
                <Label for="jamaica_address">Jamaica address</Label>
                <textarea
                    id="jamaica_address"
                    name="jamaica_address"
                    rows="3"
                    class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                    :default-value="profile.jamaica_address ?? ''"
                />
                <InputError :message="errors.jamaica_address" />
            </div>

            <div class="grid gap-2">
                <Label for="parish">Parish</Label>
                <Input
                    id="parish"
                    name="parish"
                    :default-value="profile.parish ?? ''"
                />
                <InputError :message="errors.parish" />
            </div>

            <Button
                type="submit"
                class="bg-brand-gold text-brand-ink hover:bg-brand-gold-soft"
                :disabled="processing"
            >
                Save profile
            </Button>
        </Form>
    </div>
</template>
