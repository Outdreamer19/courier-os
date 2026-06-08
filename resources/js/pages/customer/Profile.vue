<script setup lang="ts">
import { Form, Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import { edit, update } from '@/routes/portal/profile';
import { update as updatePickupPerson } from '@/routes/portal/authorised-pickup-person';

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
        trn: string | null;
        phone: string | null;
        whatsapp_number: string | null;
        jamaica_address: string | null;
        parish: string | null;
        date_of_birth: string | null;
    };
    authorisedPickupPerson: {
        full_name: string;
        phone: string;
        relationship_note: string | null;
        id_number: string | null;
    } | null;
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user);

const pickupForm = useForm({
    full_name: props.authorisedPickupPerson?.full_name ?? '',
    phone: props.authorisedPickupPerson?.phone ?? '',
    relationship_note: props.authorisedPickupPerson?.relationship_note ?? '',
    id_number: props.authorisedPickupPerson?.id_number ?? '',
});

const removePickupPerson = () => {
    pickupForm
        .transform(() => ({ remove: true }))
        .patch(updatePickupPerson().url);
};
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
                <Label for="trn">TRN</Label>
                <Input
                    id="trn"
                    name="trn"
                    :default-value="profile.trn ?? ''"
                    required
                />
                <InputError :message="errors.trn" />
            </div>

            <div class="grid gap-2">
                <Label for="phone">Phone</Label>
                <Input
                    id="phone"
                    name="phone"
                    :default-value="profile.phone ?? ''"
                    required
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
                    required
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

            <div class="grid gap-2">
                <Label for="date_of_birth">Date of birth</Label>
                <Input
                    id="date_of_birth"
                    name="date_of_birth"
                    type="date"
                    :default-value="profile.date_of_birth ?? ''"
                    required
                />
                <InputError :message="errors.date_of_birth" />
            </div>

            <Button
                type="submit"
                class="bg-brand-gold text-brand-ink hover:bg-brand-gold-soft"
                :disabled="processing"
            >
                Save profile
            </Button>
        </Form>

        <div class="max-w-2xl space-y-4 border-t pt-8">
            <Heading
                title="Authorised pickup person"
                description="Name someone who can collect packages on your behalf when you are unavailable."
            />

            <form
                class="space-y-4"
                @submit.prevent="pickupForm.patch(updatePickupPerson().url)"
            >
                <div class="grid gap-2">
                    <Label for="pickup_full_name">Full name</Label>
                    <Input
                        id="pickup_full_name"
                        v-model="pickupForm.full_name"
                        required
                    />
                    <InputError :message="pickupForm.errors.full_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="pickup_phone">Phone</Label>
                    <Input
                        id="pickup_phone"
                        v-model="pickupForm.phone"
                        required
                    />
                    <InputError :message="pickupForm.errors.phone" />
                </div>

                <div class="grid gap-2">
                    <Label for="pickup_relationship">Relationship or note</Label>
                    <Input
                        id="pickup_relationship"
                        v-model="pickupForm.relationship_note"
                        placeholder="e.g. Spouse, sibling, colleague"
                    />
                    <InputError :message="pickupForm.errors.relationship_note" />
                </div>

                <div class="grid gap-2">
                    <Label for="pickup_id_number">ID / TRN (optional)</Label>
                    <Input
                        id="pickup_id_number"
                        v-model="pickupForm.id_number"
                    />
                    <InputError :message="pickupForm.errors.id_number" />
                </div>

                <div class="flex flex-wrap gap-3">
                    <Button
                        type="submit"
                        class="bg-brand-gold text-brand-ink hover:bg-brand-gold-soft"
                        :disabled="pickupForm.processing"
                    >
                        Save pickup person
                    </Button>
                    <Button
                        v-if="authorisedPickupPerson"
                        type="button"
                        variant="outline"
                        :disabled="pickupForm.processing"
                        @click="removePickupPerson"
                    >
                        Remove
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
