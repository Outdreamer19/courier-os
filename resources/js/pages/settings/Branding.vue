<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    edit as brandingEdit,
    update as brandingUpdate,
} from '@/routes/branding';
import {
    store as uploadLogo,
    destroy as destroyLogo,
} from '@/routes/branding/logo';

type Props = {
    tenant: {
        name: string;
        currency: string;
        brand_primary_color: string | null;
        brand_accent_color: string | null;
        logo_path: string | null;
    };
    currencies: Record<string, string>;
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Branding settings', href: brandingEdit() }],
    },
});

const form = useForm({
    name: props.tenant.name,
    currency: props.tenant.currency,
    brand_primary_color: props.tenant.brand_primary_color ?? '',
    brand_accent_color: props.tenant.brand_accent_color ?? '',
});

// Logo upload state
const logoFile = ref<File | null>(null);
const logoPreview = ref<string | null>(props.tenant.logo_path);
const logoError = ref<string | null>(null);
const logoProcessing = ref(false);

function onLogoChange(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    logoError.value = null;

    if (!file) {
        return;
    }

    const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];

    if (!allowed.includes(file.type)) {
        logoError.value = 'Please upload a JPEG, PNG, WebP, or SVG image.';

        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        logoError.value = 'Logo must be under 2 MB.';

        return;
    }

    logoFile.value = file;
    logoPreview.value = URL.createObjectURL(file);
}

function submitLogo() {
    if (!logoFile.value) {
        return;
    }

    logoProcessing.value = true;
    const data = new FormData();
    data.append('logo', logoFile.value);

    useForm({ logo: logoFile.value }).post(uploadLogo().url, {
        onFinish: () => {
            logoProcessing.value = false;
        },
    });
}

function removeLogo() {
    useForm({}).delete(destroyLogo().url);
}
</script>

<template>
    <Head title="Branding settings" />

    <h1 class="sr-only">Branding settings</h1>

    <div class="flex flex-col space-y-8">
        <!-- Business identity -->
        <section>
            <Heading
                variant="small"
                title="Business identity"
                description="Your business name and default currency shown to customers."
            />

            <form
                :action="brandingUpdate().url"
                method="post"
                class="mt-4 max-w-xl space-y-4"
                @submit.prevent="form.patch(brandingUpdate().url)"
            >
                <div class="grid gap-2">
                    <Label for="name">Business name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        type="text"
                        autocomplete="organization"
                        required
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="currency">Currency</Label>
                    <select
                        id="currency"
                        v-model="form.currency"
                        class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs ring-offset-background transition-colors placeholder:text-muted-foreground focus:ring-1 focus:ring-ring focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option
                            v-for="(label, code) in currencies"
                            :key="code"
                            :value="code"
                        >
                            {{ label }}
                        </option>
                    </select>
                    <InputError :message="form.errors.currency" />
                </div>

                <div class="grid gap-2">
                    <Label for="brand_primary_color"
                        >Primary colour
                        <span class="text-muted-foreground"
                            >(optional)</span
                        ></Label
                    >
                    <div class="flex items-center gap-3">
                        <input
                            id="brand_primary_color"
                            v-model="form.brand_primary_color"
                            type="color"
                            class="h-9 w-12 cursor-pointer rounded border p-0.5"
                        />
                        <Input
                            v-model="form.brand_primary_color"
                            type="text"
                            placeholder="#12275E"
                            class="max-w-36 font-mono uppercase"
                            pattern="^(#[0-9A-Fa-f]{6})?$"
                        />
                        <button
                            v-if="form.brand_primary_color"
                            type="button"
                            class="text-sm text-muted-foreground underline hover:text-foreground"
                            @click="form.brand_primary_color = ''"
                        >
                            Clear
                        </button>
                    </div>
                    <InputError :message="form.errors.brand_primary_color" />
                    <p class="text-xs text-muted-foreground">
                        Used for buttons, links, and the sidebar throughout your
                        dashboard. Enter a 6-digit hex code.
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="brand_accent_color"
                        >Accent colour
                        <span class="text-muted-foreground"
                            >(optional)</span
                        ></Label
                    >
                    <div class="flex items-center gap-3">
                        <input
                            id="brand_accent_color"
                            v-model="form.brand_accent_color"
                            type="color"
                            class="h-9 w-12 cursor-pointer rounded border p-0.5"
                        />
                        <Input
                            v-model="form.brand_accent_color"
                            type="text"
                            placeholder="#D0202E"
                            class="max-w-36 font-mono uppercase"
                            pattern="^(#[0-9A-Fa-f]{6})?$"
                        />
                        <button
                            v-if="form.brand_accent_color"
                            type="button"
                            class="text-sm text-muted-foreground underline hover:text-foreground"
                            @click="form.brand_accent_color = ''"
                        >
                            Clear
                        </button>
                    </div>
                    <InputError :message="form.errors.brand_accent_color" />
                    <p class="text-xs text-muted-foreground">
                        Used for highlights and secondary badges. Enter a
                        6-digit hex code.
                    </p>
                </div>

                <p class="text-xs text-muted-foreground">
                    Changes apply the next time a page loads in your dashboard.
                </p>

                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Saving…' : 'Save changes' }}
                </Button>
            </form>
        </section>

        <hr class="border-border" />

        <!-- Logo -->
        <section>
            <Heading
                variant="small"
                title="Logo"
                description="Shown in the sidebar and on customer-facing pages. Max 2 MB — JPEG, PNG, WebP, or SVG."
            />

            <div class="mt-4 max-w-xl space-y-4">
                <!-- Current logo or placeholder -->
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-lg border bg-muted"
                    >
                        <img
                            v-if="logoPreview"
                            :src="logoPreview"
                            alt="Current logo"
                            class="h-full w-full object-contain"
                        />
                        <span v-else class="text-xs text-muted-foreground"
                            >No logo</span
                        >
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <Label
                            for="logo-upload"
                            class="inline-flex cursor-pointer items-center rounded-md border bg-background px-3 py-1.5 text-sm font-medium transition-colors hover:bg-accent"
                        >
                            Choose file
                        </Label>
                        <input
                            id="logo-upload"
                            type="file"
                            accept="image/jpeg,image/png,image/webp,image/svg+xml"
                            class="sr-only"
                            @change="onLogoChange"
                        />
                        <span
                            v-if="logoFile"
                            class="text-xs text-muted-foreground"
                        >
                            {{ logoFile.name }}
                        </span>
                    </div>
                </div>

                <p v-if="logoError" class="text-sm text-destructive">
                    {{ logoError }}
                </p>

                <div class="flex gap-2">
                    <Button
                        type="button"
                        :disabled="!logoFile || logoProcessing"
                        @click="submitLogo"
                    >
                        {{ logoProcessing ? 'Uploading…' : 'Upload logo' }}
                    </Button>

                    <Button
                        v-if="tenant.logo_path"
                        type="button"
                        variant="outline"
                        class="text-destructive hover:text-destructive"
                        @click="removeLogo"
                    >
                        Remove logo
                    </Button>
                </div>
            </div>
        </section>
    </div>
</template>
