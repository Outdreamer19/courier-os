<script setup lang="ts">
import { Form, Head, router, useForm, usePage } from '@inertiajs/vue3';
import { UserCircle, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';
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
import { dashboard } from '@/routes';
import { edit, update } from '@/routes/portal/profile';
import { destroy, store } from '@/routes/portal/authorised-pickup-people';
import { cn } from '@/lib/utils';

type PickupPerson = {
    id: number;
    full_name: string;
    phone: string;
    relationship_note: string | null;
    id_number: string | null;
};

type ProfileTab = 'account' | 'pickup';

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
    authorisedPickupPeople: PickupPerson[];
    maxAuthorisedPickupPeople: number;
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user);
const activeTab = ref<ProfileTab>('account');

const canAddPickupPerson = computed(
    () => props.authorisedPickupPeople.length < props.maxAuthorisedPickupPeople,
);

const pickupForm = useForm({
    full_name: '',
    phone: '',
    relationship_note: '',
    id_number: '',
});

const setTab = (tab: ProfileTab) => {
    activeTab.value = tab;
};

const addPickupPerson = () => {
    pickupForm.post(store().url, {
        preserveScroll: true,
        onSuccess: () => {
            pickupForm.reset();
            activeTab.value = 'pickup';
        },
    });
};

const removePickupPerson = (person: PickupPerson) => {
    router.delete(destroy(person.id).url, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="My profile" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
        >
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold tracking-tight">My profile</h1>
                <p class="max-w-2xl text-sm text-muted-foreground">
                    Manage your account details and the people authorised to
                    collect packages on your behalf.
                </p>
            </div>

            <Card class="w-full shrink-0 border-brand-gold/20 bg-brand-gold/5 lg:w-72">
                <CardHeader class="pb-2">
                    <CardDescription>Customer reference</CardDescription>
                    <CardTitle class="font-mono text-lg">
                        {{ profile.customer_reference }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="text-sm text-muted-foreground">
                    Use this reference on every shipment to your Florida
                    warehouse address.
                </CardContent>
            </Card>
        </div>

        <div class="space-y-6">
            <div
                role="tablist"
                aria-label="Profile sections"
                class="flex flex-wrap gap-2 border-b pb-1"
            >
                <button
                    id="profile-tab-account"
                    type="button"
                    role="tab"
                    :aria-selected="activeTab === 'account'"
                    aria-controls="profile-panel-account"
                    :class="
                        cn(
                            'inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition-colors',
                            activeTab === 'account'
                                ? 'bg-muted text-foreground'
                                : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground',
                        )
                    "
                    @click="setTab('account')"
                >
                    <UserCircle class="size-4" />
                    Account details
                </button>
                <button
                    id="profile-tab-pickup"
                    type="button"
                    role="tab"
                    :aria-selected="activeTab === 'pickup'"
                    aria-controls="profile-panel-pickup"
                    :class="
                        cn(
                            'inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition-colors',
                            activeTab === 'pickup'
                                ? 'bg-muted text-foreground'
                                : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground',
                        )
                    "
                    @click="setTab('pickup')"
                >
                    <Users class="size-4" />
                    Pickup people
                    <span
                        class="rounded-full bg-background px-2 py-0.5 text-xs tabular-nums text-muted-foreground"
                    >
                        {{ authorisedPickupPeople.length }} /
                        {{ maxAuthorisedPickupPeople }}
                    </span>
                </button>
            </div>

            <div
                v-show="activeTab === 'account'"
                id="profile-panel-account"
                role="tabpanel"
                aria-labelledby="profile-tab-account"
            >
                <Card>
                    <CardHeader>
                        <CardTitle>Account details</CardTitle>
                        <CardDescription>
                            Your contact information and Jamaica address used
                            for shipping and pickup.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form
                            v-bind="update.form()"
                            class="space-y-6"
                            v-slot="{ errors, processing }"
                        >
                            <div
                                class="grid gap-6 md:grid-cols-2 xl:grid-cols-3"
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
                                    <Label for="whatsapp_number">
                                        WhatsApp number
                                    </Label>
                                    <Input
                                        id="whatsapp_number"
                                        name="whatsapp_number"
                                        :default-value="
                                            profile.whatsapp_number ?? ''
                                        "
                                    />
                                    <InputError
                                        :message="errors.whatsapp_number"
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="date_of_birth">
                                        Date of birth
                                    </Label>
                                    <Input
                                        id="date_of_birth"
                                        name="date_of_birth"
                                        type="date"
                                        :default-value="
                                            profile.date_of_birth ?? ''
                                        "
                                        required
                                    />
                                    <InputError
                                        :message="errors.date_of_birth"
                                    />
                                </div>

                                <div class="grid gap-2 md:col-span-2">
                                    <Label for="jamaica_address">
                                        Jamaica address
                                    </Label>
                                    <textarea
                                        id="jamaica_address"
                                        name="jamaica_address"
                                        rows="3"
                                        required
                                        class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                                        :default-value="
                                            profile.jamaica_address ?? ''
                                        "
                                    />
                                    <InputError
                                        :message="errors.jamaica_address"
                                    />
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
                            </div>

                            <div class="flex justify-end border-t pt-6">
                                <Button
                                    type="submit"
                                    class="bg-brand-gold text-brand-ink hover:bg-brand-gold-soft"
                                    :disabled="processing"
                                >
                                    Save profile
                                </Button>
                            </div>
                        </Form>
                    </CardContent>
                </Card>
            </div>

            <div
                v-show="activeTab === 'pickup'"
                id="profile-panel-pickup"
                role="tabpanel"
                aria-labelledby="profile-tab-pickup"
            >
                <div class="grid gap-6 xl:grid-cols-5">
                    <Card class="xl:col-span-3">
                        <CardHeader>
                            <CardTitle>Saved pickup people</CardTitle>
                            <CardDescription>
                                Anyone listed here may collect a package for you
                                at the warehouse. Show valid ID when collecting.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div
                                v-for="person in authorisedPickupPeople"
                                :key="person.id"
                                class="rounded-lg border p-4"
                            >
                                <div
                                    class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                                >
                                    <dl
                                        class="grid flex-1 gap-3 text-sm sm:grid-cols-2"
                                    >
                                        <div>
                                            <dt class="text-muted-foreground">
                                                Name
                                            </dt>
                                            <dd class="font-medium">
                                                {{ person.full_name }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-muted-foreground">
                                                Phone
                                            </dt>
                                            <dd class="font-medium">
                                                {{ person.phone }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-muted-foreground">
                                                Relationship / note
                                            </dt>
                                            <dd class="font-medium">
                                                {{
                                                    person.relationship_note ||
                                                    '—'
                                                }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-muted-foreground">
                                                ID / TRN
                                            </dt>
                                            <dd class="font-medium">
                                                {{ person.id_number || '—' }}
                                            </dd>
                                        </div>
                                    </dl>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        class="shrink-0"
                                        @click="removePickupPerson(person)"
                                    >
                                        Remove
                                    </Button>
                                </div>
                            </div>

                            <p
                                v-if="!authorisedPickupPeople.length"
                                class="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground"
                            >
                                No authorised pickup people yet. Add someone on
                                the right when they need to collect on your
                                behalf.
                            </p>
                        </CardContent>
                    </Card>

                    <Card class="xl:col-span-2">
                        <CardHeader>
                            <CardTitle>Add pickup person</CardTitle>
                            <CardDescription>
                                You can add up to
                                {{ maxAuthorisedPickupPeople }} people.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form
                                v-if="canAddPickupPerson"
                                class="space-y-4"
                                @submit.prevent="addPickupPerson"
                            >
                                <p class="text-sm text-muted-foreground">
                                    {{ authorisedPickupPeople.length }} of
                                    {{ maxAuthorisedPickupPeople }} slots used
                                </p>

                                <div class="grid gap-2">
                                    <Label for="pickup_full_name">
                                        Full name
                                    </Label>
                                    <Input
                                        id="pickup_full_name"
                                        v-model="pickupForm.full_name"
                                        required
                                    />
                                    <InputError
                                        :message="pickupForm.errors.full_name"
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="pickup_phone">Phone</Label>
                                    <Input
                                        id="pickup_phone"
                                        v-model="pickupForm.phone"
                                        required
                                    />
                                    <InputError
                                        :message="pickupForm.errors.phone"
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="pickup_relationship">
                                        Relationship or note
                                    </Label>
                                    <Input
                                        id="pickup_relationship"
                                        v-model="pickupForm.relationship_note"
                                        placeholder="e.g. Spouse, sibling, colleague"
                                    />
                                    <InputError
                                        :message="
                                            pickupForm.errors.relationship_note
                                        "
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="pickup_id_number">
                                        ID / TRN (optional)
                                    </Label>
                                    <Input
                                        id="pickup_id_number"
                                        v-model="pickupForm.id_number"
                                    />
                                    <InputError
                                        :message="pickupForm.errors.id_number"
                                    />
                                </div>

                                <Button
                                    type="submit"
                                    class="w-full bg-brand-gold text-brand-ink hover:bg-brand-gold-soft"
                                    :disabled="pickupForm.processing"
                                >
                                    Add pickup person
                                </Button>
                            </form>

                            <p
                                v-else
                                class="rounded-lg border border-dashed p-6 text-sm text-muted-foreground"
                            >
                                You have reached the maximum of
                                {{ maxAuthorisedPickupPeople }} authorised
                                pickup people. Remove someone from the list to
                                add another.
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>
